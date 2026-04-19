/**
 * This file is loaded via the <script> tag in the index.html file and will
 * be executed in the renderer process for that window. No Node.js APIs are
 * available in this process because `nodeIntegration` is turned off and
 * `contextIsolation` is turned on. Use the contextBridge API in `preload.js`
 * to expose Node.js functionality from the main process.
 */
// renderer.js
// renderer.js
const { ipcRenderer } = require('electron');

document.getElementById('create-app').addEventListener('click', () => {
  const url = document.getElementById('url').value;
  const appName = document.getElementById('appName').value;
  const appIcon = document.getElementById('appIcon').files[0]?.path || "";
  const titleBarStyle = document.getElementById('titleBarStyle').value;
  const platform = document.getElementById('platform').value;
  const width = parseInt(document.getElementById('width').value) || 1200; // default to 1200 if not provided
  const height = parseInt(document.getElementById('height').value) || 800; // default to 800 if not provided


  ipcRenderer.send('create-app', { url, appName, appIcon, titleBarStyle, platform, width, height });
});

ipcRenderer.on('progress', (event, progress) => {
    const progressBar = document.getElementById('progress-bar');
    progressBar.style.width = `${progress}%`;
    progressBar.setAttribute('aria-valuenow', progress);
});


// Listen for error messages from the main process
ipcRenderer.on('error', (event, message) => {
    alert(message);  // Display the error message to the user
});

// Listen for log messages from the main process
ipcRenderer.on('log', (event, message) => {
    const logSection = document.getElementById('logSection');
    logSection.textContent += message + '\n';  // Append the log message to the log section
});

// Listen for error messages from the main process and display them
ipcRenderer.on('error', (event, message) => {
  alert(message);
});
