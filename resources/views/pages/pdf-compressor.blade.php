<?php

use Livewire\Volt\Component;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Kompresor PDF')] class extends Component {
    // Komponen murni UI Client-side
}; ?>
<div>
    <!-- Page Header -->
    <div style="margin-bottom: 28px;">
        <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em; line-height:1.2;">Kompresor PDF (Client-Side)</h1>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">Kecilkan ukuran file PDF Anda langsung dari browser tanpa perlu mengunggah ke server.</p>
    </div>

    <div class="grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px; align-items: start;">
        
        <!-- Kolom Kiri: Input & Kontrol -->
        <div class="card" style="padding: 20px;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-primary);">Pilih File PDF</label>
                <div style="border: 2px dashed var(--border-color); border-radius: 8px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.2s;" id="drop-zone" onclick="document.getElementById('pdf-file').click()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:40px;height:40px;color:var(--primary);margin:0 auto 12px;"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <div style="font-weight: 600; color: var(--text-primary);">Klik untuk memilih file PDF</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Atau drag & drop ke sini</div>
                    <input type="file" id="pdf-file" accept="application/pdf" style="display: none;">
                </div>
                <div id="file-name" style="margin-top: 8px; font-size: 0.85rem; font-weight: 500; color: var(--primary); text-align: center;"></div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-primary);">Tingkat Kompresi</label>
                <select id="compression-level" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-card); font-family: inherit;">
                    <option value="high">Kualitas Tinggi (Ukuran Agak Besar)</option>
                    <option value="medium" selected>Menengah (Rekomendasi)</option>
                    <option value="low">Kualitas Rendah (Ukuran Terkecil)</option>
                </select>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 6px;">
                    Catatan: Fitur ini akan merender ulang seluruh halaman PDF menjadi gambar (JPEG) lalu membungkusnya kembali menjadi PDF. Sangat cocok untuk dokumen hasil scan.
                </div>
            </div>

            <button id="compress-btn" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; margin-bottom: 12px;" disabled>
                Mulai Kompresi
            </button>
            
            <a id="download-btn" class="btn btn-success" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; display: none; background: var(--success); color: white;">
                Download PDF Terkompresi
            </a>
            
            <div id="progress-container" style="display:none; margin-top:16px;">
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600; margin-bottom:4px;">
                    <span>Progres Kompresi</span>
                    <span id="progress-text">0%</span>
                </div>
                <div style="width:100%; height:8px; background:var(--border-color); border-radius:4px; overflow:hidden;">
                    <div id="progress-bar" style="width:0%; height:100%; background:var(--primary); transition:width 0.3s;"></div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Preview -->
        <div class="card" style="padding: 20px; display:flex; flex-direction:column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-weight: 700; font-size: 1.1rem; margin: 0;">Preview Halaman</h3>
                
                <div id="page-controls" style="display: none; align-items: center; gap: 8px;">
                    <span style="font-size: 0.85rem; font-weight:600;">Halaman <span id="page-num">1</span> / <span id="page-count">1</span></span>
                </div>
            </div>

            <div id="preview-container" style="flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; min-height: 400px; overflow: auto; padding: 20px;">
                <div id="loading" style="display: none; text-align: center; color: var(--text-muted);">
                    <div class="loading-spinner mb-2"></div>
                    <div id="loading-text">Memproses...</div>
                </div>
                <div id="placeholder" style="text-align: center; color: var(--text-muted);">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;margin:0 auto 12px;opacity:0.5;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <div>Preview kompresi akan muncul di sini</div>
                </div>
                <canvas id="pdf-canvas" style="display: none; max-width: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 4px;"></canvas>
            </div>
        </div>

    </div>

    <!-- Sertakan library pdf.js dan jsPDF dari CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        // Setup worker src
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const fileInput = document.getElementById('pdf-file');
        const compressBtn = document.getElementById('compress-btn');
        const downloadBtn = document.getElementById('download-btn');
        const loading = document.getElementById('loading');
        const loadingText = document.getElementById('loading-text');
        const placeholder = document.getElementById('placeholder');
        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const canvas = document.getElementById('pdf-canvas');
        const ctx = canvas.getContext('2d');
        const pageControls = document.getElementById('page-controls');

        let originalFile = null;

        // Handle File Selection
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type === 'application/pdf') {
                originalFile = file;
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                document.getElementById('file-name').innerHTML = `<b>${file.name}</b><br><span style="color:#666">Ukuran awal: ${sizeMb} MB</span>`;
                compressBtn.disabled = false;
                downloadBtn.style.display = 'none';
                progressContainer.style.display = 'none';
            } else {
                alert('Silakan pilih file PDF yang valid.');
            }
        });

        // Handle Drop Zone
        const dropZone = document.getElementById('drop-zone');
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--primary)';
            dropZone.style.background = 'var(--primary-light)';
        });
        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--border-color)';
            dropZone.style.background = 'transparent';
        });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--border-color)';
            dropZone.style.background = 'transparent';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });

        // Handle Compress
        compressBtn.addEventListener('click', async () => {
            if (!originalFile) return;

            compressBtn.disabled = true;
            downloadBtn.style.display = 'none';
            placeholder.style.display = 'none';
            canvas.style.display = 'none';
            loading.style.display = 'block';
            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';
            progressText.innerText = '0%';
            loadingText.innerText = 'Membaca file PDF...';

            try {
                // Konfigurasi Kualitas
                const level = document.getElementById('compression-level').value;
                let scale = 1.5;
                let quality = 0.6;
                
                if (level === 'high') { scale = 2.0; quality = 0.8; }
                if (level === 'low') { scale = 1.0; quality = 0.4; }

                const arrayBuffer = await originalFile.arrayBuffer();
                const typedarray = new Uint8Array(arrayBuffer);
                const pdf = await pdfjsLib.getDocument(typedarray).promise;
                const numPages = pdf.numPages;
                
                document.getElementById('page-count').textContent = numPages;
                pageControls.style.display = 'flex';

                const { jsPDF } = window.jspdf;
                let newPdf = null;

                for (let i = 1; i <= numPages; i++) {
                    loadingText.innerText = `Mengompresi Halaman ${i} dari ${numPages}...`;
                    document.getElementById('page-num').textContent = i;
                    
                    const page = await pdf.getPage(i);
                    const viewport = page.getViewport({ scale: scale });

                    // Setup temporary canvas for rendering
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    
                    // Render PDF page into canvas
                    await page.render({
                        canvasContext: ctx,
                        viewport: viewport
                    }).promise;
                    
                    canvas.style.display = 'block'; // Tampilkan progres secara live
                    
                    // Ekstrak sebagai JPEG terkompresi
                    const imgData = canvas.toDataURL('image/jpeg', quality);

                    // Dimensi untuk jsPDF (menggunakan points/px)
                    const isLandscape = viewport.width > viewport.height;
                    const orientation = isLandscape ? 'l' : 'p';
                    const format = [viewport.width, viewport.height];

                    if (i === 1) {
                        newPdf = new jsPDF({
                            orientation: orientation,
                            unit: 'px',
                            format: format,
                            hotfixes: ["px_scaling"]
                        });
                    } else {
                        newPdf.addPage(format, orientation);
                    }

                    // Tambahkan ke PDF baru
                    newPdf.addImage(imgData, 'JPEG', 0, 0, viewport.width, viewport.height);

                    // Update Progress
                    const progressPercent = Math.round((i / numPages) * 100);
                    progressBar.style.width = `${progressPercent}%`;
                    progressText.innerText = `${progressPercent}%`;
                }

                loadingText.innerText = 'Menyimpan file PDF baru...';
                
                // Ambil file output
                const pdfBlob = newPdf.output('blob');
                const compressedUrl = URL.createObjectURL(pdfBlob);
                
                // Update UI Download
                const finalSizeMb = (pdfBlob.size / (1024 * 1024)).toFixed(2);
                document.getElementById('file-name').innerHTML += `<br><span style="color:var(--success); font-weight:bold;">Ukuran terkompresi: ${finalSizeMb} MB</span>`;
                
                downloadBtn.href = compressedUrl;
                downloadBtn.download = originalFile.name.replace('.pdf', '_compressed.pdf');
                downloadBtn.style.display = 'flex';
                
                loading.style.display = 'none';
                compressBtn.disabled = false;
                compressBtn.innerText = 'Kompres Lagi';
                
            } catch (error) {
                console.error("Kompresi Gagal: ", error);
                alert("Terjadi kesalahan saat mengompresi PDF.");
                loading.style.display = 'none';
                compressBtn.disabled = false;
            }
        });
    </script>
</div>
