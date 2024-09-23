<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retro File Decryptor 3.0</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #000;
            color: #00ff00;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background-color: #000;
            border: 4px solid #00ff00;
            padding: 20px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 0 0 5px #00ff00;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input, button {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
            background-color: #000;
            border: 1px solid #00ff00;
            color: #00ff00;
            font-family: inherit;
        }
        button {
            background-color: #00ff00;
            color: #000;
            border: none;
            padding: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        button:hover {
            background-color: #00cc00;
            box-shadow: 0 0 10px #00ff00;
        }
        #output {
            margin-top: 20px;
            border: 1px solid #00ff00;
            padding: 10px;
            height: 150px;
            overflow-y: auto;
        }
        .nav-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #00ff00;
            text-decoration: none;
        }
        .nav-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Retro File Decryptor 3.0</h1>
        <div>
            <label for="fileInput">Select Encrypted File:</label>
            <input type="file" id="fileInput">
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" placeholder="Enter password">
        </div>
        <button onclick="decryptFile()">Decrypt File</button>
        <div id="output"></div>
        <a href="index.php" class="nav-link">Go to File Encryptor</a>
    </div>

    <script>
        async function decryptFile() {
            const fileInput = document.getElementById('fileInput');
            const password = document.getElementById('password').value;
            const output = document.getElementById('output');

            if (!fileInput.files.length || !password) {
                output.innerHTML = 'Please select a file and enter a password.';
                return;
            }

            const file = fileInput.files[0];
            output.innerHTML = `Reading file: ${file.name}\n`;

            try {
                const fileContent = await readFileAsArrayBuffer(file);
                output.innerHTML += `File read. Size: ${fileContent.byteLength} bytes\n`;

                const salt = new Uint8Array(fileContent.slice(0, 16));
                const iv = new Uint8Array(fileContent.slice(16, 28));
                const data = new Uint8Array(fileContent.slice(28));
                output.innerHTML += `Extracted salt and IV\n`;

                const keyMaterial = await crypto.subtle.importKey(
                    "raw",
                    new TextEncoder().encode(password),
                    { name: "PBKDF2" },
                    false,
                    ["deriveBits", "deriveKey"]
                );

                const key = await crypto.subtle.deriveKey(
                    {
                        name: "PBKDF2",
                        salt: salt,
                        iterations: 100000,
                        hash: "SHA-256"
                    },
                    keyMaterial,
                    { name: "AES-GCM", length: 256 },
                    true,
                    ["decrypt"]
                );
                output.innerHTML += `Key derived\n`;

                const decrypted = await crypto.subtle.decrypt(
                    { name: "AES-GCM", iv: iv },
                    key,
                    data
                );
                output.innerHTML += `File decrypted\n`;

                const blob = new Blob([decrypted], { type: 'application/octet-stream' });
                const downloadUrl = URL.createObjectURL(blob);

                const downloadLink = document.createElement('a');
                downloadLink.href = downloadUrl;
                downloadLink.download = file.name.replace('.encrypted', '') || 'decrypted_file';
                downloadLink.innerHTML = 'Download Decrypted File';
                output.appendChild(downloadLink);

                output.innerHTML += `\nDecryption complete! Click the link to download.`;
            } catch (error) {
                output.innerHTML += `Error: ${error.message}. This could be due to an incorrect password or corrupted file.`;
            }
        }

        function readFileAsArrayBuffer(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (event) => resolve(event.target.result);
                reader.onerror = (error) => reject(error);
                reader.readAsArrayBuffer(file);
            });
        }
    </script>
</body>
</html>