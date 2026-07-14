<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Crypto Withdrawal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9f9f9; }
        .container { max-width: 500px; margin: 40px auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        label { display: block; margin-top: 15px; color: #444; }
        select, input[type="text"], input[type="number"] {
            width: 100%; padding: 10px; margin-top: 5px;
            border-radius: 5px; border: 1px solid #ccc;
        }
        button {
            width: 100%; margin-top: 20px; padding: 12px;
            background-color: #007BFF; color: white;
            border: none; border-radius: 5px;
            font-size: 16px; cursor: pointer;
        }
        button:hover { background-color: #0056b3; }
    </style>
    <script>
        function updateNetworks() {
            var coin = document.getElementById("coin").value;
            var network = document.getElementById("network");
            network.innerHTML = "";

            if (coin === "BTC") {
                network.innerHTML += "<option value='BTC-Native'>BTC Native</option>";
            } else if (coin === "USDT") {
                network.innerHTML += "<option value='ERC20'>ERC20</option>";
                network.innerHTML += "<option value='TRC20'>TRC20</option>";
                network.innerHTML += "<option value='BEP20'>BEP20</option>";
            } else if (coin === "ETH") {
                network.innerHTML += "<option value='ERC20'>ERC20</option>";
            }
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Crypto Withdrawal</h2>
    <form action="process_withdraw.php" method="post">
        <input type="hidden" name="method" value="crypto">
        <label for="coin">Select Coin:</label>
        <select name="coin" id="coin" onchange="updateNetworks()" required>
            <option value="">-- Select Coin --</option>
            <option value="BTC">Bitcoin (BTC)</option>
            <option value="USDT">Tether (USDT)</option>
            <option value="ETH">Ethereum (ETH)</option>
        </select>

        <label for="network">Select Network:</label>
        <select name="network" id="network" required></select>

        <label for="wallet">Wallet Address:</label>
        <input type="text" name="wallet" required>

        <label for="amount">Amount:</label>
        <input type="number" name="amount" step="0.01" required>

        <button type="submit">Submit Withdrawal</button>
    </form>
</div>
</body>
</html>
