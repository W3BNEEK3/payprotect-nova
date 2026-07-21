<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Repositories\MailSettingsRepository;
use App\Providers\Mail\MailProviderFactory;
use App\Helpers\Crypto;

class MailSettingsController extends BaseController
{
    public function index()
    {
        $repo = new MailSettingsRepository();
        $active = $repo->getActive();

        $driver = $active ? $active['driver'] : 'smtp';
        $config = $active && !empty($active['config']) ? json_decode($active['config'], true) : [];

        // Check if we have encrypted secrets
        $hasSecret = !empty($config['secret_encrypted']);

        return $this->view('admin/mail-settings/index', [
            'driver' => $driver,
            'config' => $config,
            'hasSecret' => $hasSecret
        ]);
    }

    public function update()
    {
        $request = new Request();
        $driver = $request->input('driver');

        if (!in_array($driver, ['smtp', 'resend'])) {
            Session::flash('settings_error', 'Invalid driver selected');
            $this->redirect('/admin/mail-settings');
            return;
        }

        $repo = new MailSettingsRepository();
        $active = $repo->getActive();
        $config = $active && !empty($active['config']) ? json_decode($active['config'], true) : [];

        if ($driver === 'smtp') {
            $config['host'] = $request->input('smtp_host');
            $config['port'] = (int) $request->input('smtp_port');
            $config['username'] = $request->input('smtp_username');
            $config['encryption'] = $request->input('smtp_encryption');
            $config['from_address'] = $request->input('smtp_from_address');
            $config['from_name'] = $request->input('smtp_from_name');
            
            $password = $request->input('smtp_password');
            if (!empty($password)) {
                $config['secret_encrypted'] = base64_encode(Crypto::encrypt($password));
            }
        } elseif ($driver === 'resend') {
            $config['from_address'] = $request->input('resend_from_address');
            $config['from_name'] = $request->input('resend_from_name');

            $apiKey = $request->input('resend_api_key');
            if (!empty($apiKey)) {
                $config['secret_encrypted'] = base64_encode(Crypto::encrypt($apiKey));
            }
        }

        $jsonConfig = json_encode($config);
        $db = Database::connection();

        if ($active) {
            $stmt = $db->prepare('UPDATE mail_settings SET driver = ?, config = ?, is_active = 1 WHERE id = ?');
            $stmt->execute([$driver, $jsonConfig, $active['id']]);
        } else {
            $stmt = $db->prepare('INSERT INTO mail_settings (driver, config, is_active) VALUES (?, ?, 1)');
            $stmt->execute([$driver, $jsonConfig]);
        }

        Session::flash('settings_success', 'Mail settings updated successfully');
        $this->redirect('/admin/mail-settings');
    }

    public function testMail()
    {
        $request = new Request();
        $to = $request->input('test_email');

        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['status' => 'error', 'message' => 'Valid email address required'], 400);
        }

        try {
            $factory = new MailProviderFactory(new MailSettingsRepository());
            $mailer = $factory->make();
            $subject = 'Test Email from NovaTrust';
            $body = \App\Helpers\MailTemplate::render('test_email', [
                'subject' => $subject
            ]);
            $mailer->send($to, $subject, $body);
            return $this->json(['status' => 'success', 'message' => 'Test email sent successfully']);
        } catch (\Throwable $e) {
            $logMessage = "[" . date('Y-m-d H:i:s') . "] Mail Test Error: " . $e->getMessage() . PHP_EOL;
            file_put_contents(__DIR__ . '/../../../storage/logs/mail.log', $logMessage, FILE_APPEND);
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
