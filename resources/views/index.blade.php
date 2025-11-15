<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SAP Resume Classifier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            max-width: 600px;
            margin: auto;
        }
        #output {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            white-space: pre-wrap;
            font-family: monospace;
        }
        .btn {
            margin-top: 10px;
            padding: 10px 20px;
            background: black;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <h2>Upload Resume for SAP Role Matching</h2>

    <input type="file" id="resumeFile" accept=".pdf,.doc,.docx,.txt" />
    <br>
    <button class="btn" onclick="uploadResume()">Analyze Resume</button>

    <h3>Result:</h3>
    <div id="output">No result yet…</div>

    <script>
        async function uploadResume() {
            const fileInput = document.getElementById("resumeFile");
            const output = document.getElementById("output");

            if (!fileInput.files.length) {
                alert("Please select a resume file first.");
                return;
            }

            const file = fileInput.files[0];
            const formData = new FormData();
            formData.append("resume", file);

            output.textContent = "⏳ Uploading & analyzing… Please wait.";

            try {
                // Step 1: Upload resume to Laravel
                const uploadResponse = await fetch("http://127.0.0.1:8000/api/resume", {
                    method: "POST",
                    body: formData
                });

                const uploadJson = await uploadResponse.json();

                if (!uploadResponse.ok) {
                    output.textContent = "❌ Error uploading resume:\n" + JSON.stringify(uploadJson, null, 2);
                    return;
                }

                const resumeId = uploadJson.resume_id;
                
                // Step 2: Call /api/match
                const matchResponse = await fetch("http://127.0.0.1:8000/api/match", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ resume_id: resumeId })
                });

                const matchJson = await matchResponse.json();

                output.textContent = JSON.stringify(matchJson, null, 2);

            } catch (err) {
                output.textContent = "🔥 JavaScript Error:\n" + err;
            }
        }
    </script>
</body>
</html>
