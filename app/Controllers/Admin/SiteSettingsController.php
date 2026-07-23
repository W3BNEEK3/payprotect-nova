<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Repositories\SiteSettingsRepository;

class SiteSettingsController extends BaseController
{
    private SiteSettingsRepository $settingsRepo;

    public function __construct()
    {
        $this->settingsRepo = new SiteSettingsRepository();
    }

    public function index()
    {
        $settings = $this->settingsRepo->all();

        return $this->view('admin/site-settings/index', [
            'pageTitle' => 'Site Settings',
            'settings'  => $settings
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/site-settings');
        }

        $fields = [
            'site_name',
            'site_logo_url',
            'site_favicon_url',
            'site_address',
            'site_support_email'
        ];

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = trim($_POST[$field] ?? '');
        }

        if (empty($data['site_name'])) {
            Session::flash('error', 'Site Name is required.');
            return $this->redirect('/admin/site-settings');
        }

        $success = $this->settingsRepo->setMultiple($data);

        if ($success) {
            Session::flash('success', 'Site settings updated successfully.');
        } else {
            Session::flash('error', 'Failed to update site settings.');
        }

        return $this->redirect('/admin/site-settings');
    }
}
