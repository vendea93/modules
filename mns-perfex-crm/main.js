const { app, BrowserWindow, ipcMain, shell } = require("electron");
const path = require("path");
const { exec } = require("child_process");

let notifCount = 0;

function createWindow() {
  
  
  const win = new BrowserWindow({
    
    width: 1200,
    height: 800,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
    }
  });

  win.loadFile("index.html");


  ipcMain.on("new-notification", () => {
    notifCount += 1;
    app.setBadgeCount(notifCount);
  });

  ipcMain.on("notification-clicked", () => {
    notifCount = 0;
    app.setBadgeCount(notifCount);
  });

  win.webContents.on("did-start-loading", () => {
    win.setProgressBar(2); // Indeterminate progress bar
  });

  win.webContents.on("did-stop-loading", () => {
    win.setProgressBar(-1); // Hide progress bar
  });
}

app.whenReady().then(createWindow);

app.on("window-all-closed", () => {
  if (process.platform !== "darwin") {
    app.quit();
  }
});

app.on("activate", () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    createWindow();
  }
});

ipcMain.on("create-app", (event, options) => {
  const desktopDir = path.join(require("os").homedir(), "Desktop");
  const appDir = path.join(desktopDir, options.appName.replace(/ /g, "_"));

  const platform = options.platform ? `--platform "${options.platform}"` : "";
  if (!options.url || !options.appName) {
    event.sender.send('error', 'Please ensure the URL and Application Name fields are filled.');
    return;
  }


    const iconOption = options.appIcon ? `--icon "${options.appIcon}"` : "";


    if (iconOption) {
        event.sender.send('log', `Using provided icon path: ${iconOption}`);
    }


  let titleBarStyle = "";
  if (options.titleBarStyle !== "default") {
    titleBarStyle = `--title-bar-style "${options.titleBarStyle}"`;
  }

  const command = `nativefier --name "${options.appName}"  ${titleBarStyle} ${platform} ${iconOption} "${options.url}" "${appDir}"`;

  event.sender.send("progress", 50);

  
    event.sender.send('log', `Executing command: ${command}`);

    exec(command, (error, stdout, stderr) => {
    if (error) {
      
    console.error(`Error running Nativefier: ${error}`);
    // Send an error message back to the renderer process
    event.sender.send('error', `Error running Nativefier: ${error}`);
    
      return;
    }

    event.sender.send("progress", 100);

    shell.openPath(appDir);

    event.sender.send('log', `stdout: ${stdout}`);
    event.sender.send('log', `stderr: ${stderr}`);
  if (error) {
    event.sender.send('error', 'There was an error creating the application. Please check the logs for details.');
    event.sender.send('log', `Error: ${error}`);
    return;
  }

  });
});
