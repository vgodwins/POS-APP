<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">Scan Voucher QR</div>
      <div class="card-body">
        <p class="text-muted">Allow camera access and point at the voucher QR to verify automatically.</p>
        <video id="video" width="640" height="480" style="max-width:100%; border:1px solid #ddd; border-radius:8px" autoplay></video>
        <canvas id="canvas" width="640" height="480" style="display:none"></canvas>
        <div id="scanResult" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
  const video = document.getElementById('video');
  const canvas = document.getElementById('canvas');
  const ctx = canvas.getContext('2d');
  const resultDiv = document.getElementById('scanResult');
  let scanning = false;

  function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
      .then(stream => { video.srcObject = stream; video.play(); scanning = true; requestAnimationFrame(scanFrame); })
      .catch(err => { resultDiv.textContent = 'Camera access denied: ' + err; });
  }

  function scanFrame() {
    if (!scanning) return;
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
      canvas.width = video.videoWidth; canvas.height = video.videoHeight;
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
      const code = jsQR(imageData.data, imageData.width, imageData.height);
      if (code && code.data) {
        scanning = false;
        handleScan(code.data);
        return;
      }
    }
    requestAnimationFrame(scanFrame);
  }

  function handleScan(text) {
    resultDiv.textContent = 'Scanned: ' + text;
    try {
      const url = new URL(text);
      if (url.pathname.startsWith('/vouchers/verify')) {
        window.location.href = text;
        return;
      }
    } catch (_) { /* not a full URL */ }
    // Treat as voucher code; call JSON validation
    fetch('/vouchers/validate?code=' + encodeURIComponent(text))
      .then(r => r.json())
      .then(data => {
        if (data.ok) {
          resultDiv.innerHTML = '<div class="alert alert-success">Voucher is valid. Value: ' + Number(data.value).toFixed(2) + '</div>';
        } else {
          resultDiv.innerHTML = '<div class="alert alert-danger">Invalid: ' + (data.error || 'Unknown') + '</div>';
        }
      })
      .catch(() => { resultDiv.innerHTML = '<div class="alert alert-danger">Verification failed</div>'; });
  }

  startCamera();
</script>
