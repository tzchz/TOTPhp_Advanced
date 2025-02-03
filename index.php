<?php
function encrypt($str, $password) {
    $password = str_replace(":", "", $password);
    $result = '';
    $passwordIndex = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        $char = $str[$i];
        $passwordChar = $password[$passwordIndex];
        $result .= chr(ord($char) ^ ord($passwordChar));
        $passwordIndex = ($passwordIndex + 1) % strlen($password);
    }
    $hash = md5($str);
    return $result . ':' . $hash;
}

function decrypt($str, $password) {
    $password = str_replace(":", "", $password);
    
    $parts = explode(':', $str);
    if (count($parts) != 2) {
        return false;
    }
    $encryptedString = $parts[0];
    $expectedHash = $parts[1];
    $result = '';
    $passwordIndex = 0;
    for ($i = 0; $i < strlen($encryptedString); $i++) {
        $char = $encryptedString[$i];
        $passwordChar = $password[$passwordIndex];
        $result .= chr(ord($char) ^ ord($passwordChar));
        $passwordIndex = ($passwordIndex + 1) % strlen($password);
    }
    $actualHash = md5($result);
    if ($actualHash !== $expectedHash) {
        return false;
    }
    return $result;
}

function base32_decode($b32) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $out = '';
    $l = strlen($b32);
    $n = 0;
    $j = 0;

    for ($i = 0; $i < $l; $i++) {
        $n = $n << 5;
        $n = $n + strpos($alphabet, $b32[$i]);
        $j = $j + 5;

        if ($j >= 8) {
            $j = $j - 8;
            $out .= chr(($n & (0xFF << $j)) >> $j);
        }
    }
    return $out;
}

function get_totp_code($key) {
    $key = base32_decode($key);
    $time = floor(time() / 30);
    $time = pack('N*', 0) . pack('N*', $time);
    $hm = hash_hmac('sha1', $time, $key, true);
    $offset = ord($hm[19]) & 0xf;
    $code = ((
        (ord($hm[$offset + 0]) & 0x7f) << 24 |
        (ord($hm[$offset + 1]) & 0xff) << 16 |
        (ord($hm[$offset + 2]) & 0xff) << 8 |
        (ord($hm[$offset + 3]) & 0xff)
    ) % 1000000);
    return str_pad($code, 6, '0', STR_PAD_LEFT);
}

if($_GET['id'] and $_GET['key'] and $_GET['nfc']){
    $pattern = '/^[a-zA-Z0-9@._-]+$/';
    if(!preg_match($pattern, $_GET['id']))die('<script>alert("Check your Account ID");location = ".";</script>');
    
    $pattern = '/^[A-Z2-7]+$/i';
    if(!preg_match($pattern, $_GET['key']))die('<script>alert("Check your 2FA Key");location = ".";</script>');
    
    $pattern = '/^([0-9a-f]{2}:){3}[0-9a-f]{2}$/';
    if(!preg_match($pattern, $_GET['nfc']))die('<script>alert("Check your NFC Card");location = ".";</script>');
    
    $key = encrypt($_GET['key'], $_GET['nfc']);
    
    $db = new SQLite3('sqlite.db');
    $key = $db->escapeString($key);
    
    $db->exec("INSERT INTO tab0 (id, key) VALUES ('".$_GET['id']."', '".$key."')");
    
    $db->close();
    
    die('<script>alert("Success");location = ".";</script>');

}

$id = isset($_POST['nid']) ? $_POST['nid'] : '';
$nfc = isset($_POST['ncard']) ? $_POST['ncard'] : '';
if ($id and $nfc) {
    $db = new SQLite3('sqlite.db');
    $results = $db->query("SELECT * FROM tab0 WHERE id = '$id'");

    while ($row = $results->fetchArray()) {
        $key = $row['key'];
    }

    $db->close();
    
    $key = decrypt($key, $nfc);
    if(!$key)die('<script>alert("Verification Failed");location = ".";</script>');
    
    echo '<script>
    window.onload = function() {
        navigator.clipboard.writeText("' . get_totp_code($key) . '").then(function() {
            alert("TOTP ' . get_totp_code($key) . ' Copied");
            location = ".";
        }).catch(function(error) {
            alert("Clipboard Permission Denied");
            location = ".";
        });
    }
</script>';
    exit;

} else {
    $db = new SQLite3('sqlite.db');

    $results = $db->query('SELECT * FROM tab0');

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TOTPhp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://assets.060418.best/favicon.ico">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
      window.addEventListener('beforeinstallprompt', event => {
          event.userChoice.then(result => {console.log(result.outcome)})
        }
      )
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="app.js"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f0f0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin: 10px 0;
        }
        button {
            padding: 10px 20px;
            border: none;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 250px;
            text-align: left;
        }
        button:hover {
            background-color: #45a049;
        }
        .add-button {
            background-color: #008CBA;
            width: auto;
        }
        .add-button:hover {
            background-color: #007bb5;
        }
        #top-right-corner {
            position: fixed;
            top: 0;
            right: 0;
        }
        .icon {
            margin-right: 10px;
        }
        .title {
            font-size: 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .title i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <ul>
        <li><div class="title">
            <i class="fas fa-key"></i>
            <span>TOTPhp</span>
        </div>
        </li>
        <?php
        while ($row = $results->fetchArray()) {
            echo '<li><button id="' . $row['id'] . '" onclick="readNFC(\'' . $row['id'] . '\');"><i class="fas fa-id-badge icon"></i>' . $row['id'] . '</button></li>';
        }
        ?>
        <form id="nfcForm" method="post" action=".">
          <input type="hidden" name=nid id="AccountID" />
          <input type="hidden" name=ncard id="CardID" />
        </form>
        <script>
            async function readNFC(id) {
                try {
                    if ('NDEFReader' in window) {
                        if(id)var x = document.getElementById(id);
                        else var x = document.getElementById("add");
                        
                        x.innerHTML = "Waiting…";
        
                        const reader = new NDEFReader();
        
                        reader.scan().then(() => {
                            reader.addEventListener("reading", ({ serialNumber }) => {
                                if(id){
                                    document.getElementById('AccountID').value = id;
                                    document.getElementById('CardID').value = serialNumber;
                                    document.getElementById('nfcForm').submit();
                                }
                                else window.location.replace('?key=' + document.getElementById('nkey').value + '&nfc=' + serialNumber + '&id=' + document.getElementById('nid').value);;
                            });
                        }).catch(error => {
                            alert("Error reading NFC: " + error);
                        });
        
                    } else {
                        alert("NFC NOT Supported");
                    }
                } catch (error) {
                    console.error("Failed to Read NFC", error);
                }
            }
        </script>
        <input type="hidden" id="nid" />
        <div id="top-right-corner">
            <a href="https://github.com/tzchz/TOTPhp_Advanced">
                <img decoding="async" width="149" height="149" src="https://github.blog/wp-content/uploads/2008/12/forkme_right_darkblue_121621.png" class="attachment-full size-full" alt="Fork me on GitHub" loading="lazy">
            </a>
        </div>
        <input type="hidden" id="nkey" />
        <li><button id="add" class="add-button" onclick="document.getElementById('nid').value=prompt('Enter Account ID');document.getElementById('nkey').value=prompt('Enter 2FA Key');document.getElementById('nkey').value=document.getElementById('nkey').value.replace(/\s/g, '').toUpperCase();readNFC(0);"><i class="fas fa-plus icon"></i>Add</button></li>
    </ul>
</body>
</html><?
    $db->close();
}