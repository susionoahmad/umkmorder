/**
 * Detects if the current environment is running inside a mobile WebView.
 * Checks commonly used UserAgent strings and the presence of native bridges.
 */
export function isWebView(): boolean {
  if (typeof window === 'undefined') return false;

  // 1. Check if the custom AndroidBridge is injected
  if ((window as any).AndroidBridge) return true;

  // 2. Check User Agent signatures for webview
  const ua = window.navigator.userAgent || '';
  const isAndroidWebView = ua.includes('wv') || (ua.includes('Android') && ua.includes('Version/'));
  const isIOSWebView = (ua.includes('iPhone') || ua.includes('iPad')) && !ua.includes('Safari');

  return isAndroidWebView || isIOSWebView;
}

/**
 * Interface representing the native bridge interface (e.g. for Android WebAppInterface).
 */
interface NativeAndroidBridge {
  downloadFile?: (base64Data: string, filename: string, mimeType: string) => void;
  showToast?: (message: string) => void;
}

/**
 * Handles file downloads in a WebView-friendly manner.
 * 
 * @param base64Data Base64 representation of the file (optionally with data URI scheme prefix).
 * @param filename Default filename to save.
 * @param mimeType Mime-type of the file.
 */
export function downloadFileInWebView(base64Data: string, filename: string, mimeType: string): boolean {
  if (typeof window === 'undefined') return false;

  const cleanBase64 = base64Data.replace(/^data:.*;base64,/, '');

  // 1. Try to call the custom AndroidBridge if injected
  const bridge = (window as any).AndroidBridge as NativeAndroidBridge | undefined;
  if (bridge && typeof bridge.downloadFile === 'function') {
    try {
      bridge.downloadFile(cleanBase64, filename, mimeType);
      return true;
    } catch (err) {
      console.error('Failed to call AndroidBridge.downloadFile:', err);
    }
  }

  // 2. Fallback: If in WebView but no bridge exists, open the file data in a new tab
  // so the user can interact with it (e.g., long press to save image).
  try {
    const isImage = mimeType.startsWith('image/');
    const newWindow = window.open();
    if (newWindow) {
      newWindow.document.write(`
        <html>
          <head>
            <title>${filename}</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
              body {
                margin: 0;
                background-color: #020617;
                color: #f8fafc;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                font-family: sans-serif;
                padding: 20px;
                box-sizing: border-box;
                text-align: center;
              }
              img {
                max-width: 100%;
                max-height: 60vh;
                object-fit: contain;
                border-radius: 12px;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
                background: white;
                padding: 10px;
                margin-top: 15px;
              }
              .card {
                background: rgba(15, 23, 42, 0.6);
                border: 1px solid #1e293b;
                padding: 24px;
                border-radius: 20px;
                max-width: 400px;
                width: 100%;
              }
              h3 { margin-top: 0; color: #818cf8; }
              p { font-size: 14px; color: #94a3b8; line-height: 1.5; margin-bottom: 20px; }
              .btn-close {
                background: #6366f1;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 8px;
                cursor: pointer;
                font-weight: bold;
                margin-top: 15px;
              }
            </style>
          </head>
          <body>
            <div class="card">
              <h3>${isImage ? 'Unduh Gambar' : 'Unduh Dokumen'}</h3>
              <p>${isImage 
                ? 'Silakan tekan lama pada gambar di bawah ini, lalu pilih <b>"Simpan Gambar"</b> atau <b>"Download Gambar"</b>.' 
                : 'Silakan salin isi file di bawah ini.'}</p>
              ${isImage 
                ? `<img src="data:${mimeType};base64,${cleanBase64}" alt="${filename}" />`
                : `<textarea readonly style="width:100%; height:150px; background:#020617; color:#f8fafc; border:1px solid #334155; border-radius:8px; padding:10px; font-family:monospace; font-size:11px;">${atob(cleanBase64)}</textarea>`
              }
              <br><br>
              <button class="btn-close" onclick="window.close()">Tutup Halaman</button>
            </div>
          </body>
        </html>
      `);
      newWindow.document.close();
      return true;
    }
  } catch (err) {
    console.error('Failed to open fallback window:', err);
  }

  return false;
}
