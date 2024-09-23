<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retro File Encryptor 3.0</title>
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
        <h1>Retro File Encryptor 3.0</h1>
        <div>
            <label for="fileInput">Select File:</label>
            <input type="file" id="fileInput">
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" placeholder="Enter password">
        </div>
        <button onclick="encryptFile()">Encrypt File</button>
        <div id="output"></div>
        <a href="decryptfile.php" class="nav-link">Go to File Decryptor</a>
    </div>

    <script>
        async function encryptFile() {
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

                const salt = crypto.getRandomValues(new Uint8Array(16));
                const iv = crypto.getRandomValues(new Uint8Array(12));
                output.innerHTML += `Generated salt and IV\n`;

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
                    ["encrypt"]
                );
                output.innerHTML += `Key derived\n`;

                const encrypted = await crypto.subtle.encrypt(
                    { name: "AES-GCM", iv: iv },
                    key,
                    fileContent
                );
                output.innerHTML += `File encrypted\n`;

                const encryptedArray = new Uint8Array(encrypted);
                const result = new Uint8Array(salt.length + iv.length + encryptedArray.length);
                result.set(salt, 0);
                result.set(iv, salt.length);
                result.set(encryptedArray, salt.length + iv.length);

                const blob = new Blob([result], { type: 'application/octet-stream' });
                const downloadUrl = URL.createObjectURL(blob);

                const downloadLink = document.createElement('a');
                downloadLink.href = downloadUrl;
                downloadLink.download = file.name + '.encrypted';
                downloadLink.innerHTML = 'Download Encrypted File';
                output.appendChild(downloadLink);

                output.innerHTML += `\nEncryption complete! Click the link to download.`;
            } catch (error) {
                output.innerHTML += `Error: ${error.message}`;
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