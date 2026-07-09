<?php

use Livewire\Volt\Component;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Konverter PDF ke Gambar')] class extends Component {
    // Komponen murni UI Client-side
}; ?>
<div>
    <!-- Page Header -->
    <div style="margin-bottom: 28px;">
        <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em; line-height:1.2;">Konverter PDF ke Gambar</h1>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">Ubah file PDF menjadi gambar JPG/PNG langsung dari browser Anda tanpa membebani server.</p>
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

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-primary);">Kualitas Rendering</label>
                <select id="scale" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-card); font-family: inherit;">
                    <option value="1.0">Standar (1.0x)</option>
                    <option value="1.5">Bagus (1.5x)</option>
                    <option value="2.0" selected>Tinggi (2.0x)</option>
                    <option value="3.0">Sangat Tinggi (3.0x)</option>
                </select>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-primary);">Format Output</label>
                <div style="display: flex; gap: 12px;">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="radio" name="format" value="image/jpeg" checked> JPG
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="radio" name="format" value="image/png"> PNG
                    </label>
                </div>
            </div>

            <button id="render-btn" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; margin-bottom: 12px;" disabled>
                Proses Konversi
            </button>
            
            <a id="download-btn" class="btn btn-success" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; display: none; background: var(--success); color: white; margin-bottom: 8px;">
                Download Halaman Ini
            </a>
            
            <button id="download-all-btn" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; display: none; background: #3b82f6; color: white; margin-bottom: 8px;">
                Download Semua (ZIP)
            </button>

            <button id="download-merged-btn" class="btn btn-warning" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; display: none; background: #f59e0b; color: white;">
                Download (Gabung 1 Gambar)
            </button>
            
            <div id="zip-progress" style="display:none; text-align:center; font-size:0.85rem; color:var(--text-muted); margin-top:8px;"></div>
        </div>

        <!-- Kolom Kanan: Preview -->
        <div class="card" style="padding: 20px; display:flex; flex-direction:column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-weight: 700; font-size: 1.1rem; margin: 0;">Preview Hasil</h3>
                
                <div id="page-controls" style="display: none; align-items: center; gap: 8px;">
                    <button class="btn btn-sm" id="prev-btn" style="border:1px solid #ddd; background:white;">&laquo; Prev</button>
                    <span style="font-size: 0.85rem; font-weight:600;">Halaman <span id="page-num">1</span> / <span id="page-count">1</span></span>
                    <button class="btn btn-sm" id="next-btn" style="border:1px solid #ddd; background:white;">Next &raquo;</button>
                </div>
            </div>

            <div id="preview-container" style="flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; min-height: 400px; overflow: auto; padding: 20px;">
                <div id="loading" style="display: none; text-align: center; color: var(--text-muted);">
                    <div class="loading-spinner mb-2"></div>
                    <div id="loading-text">Memproses PDF...</div>
                </div>
                <div id="placeholder" style="text-align: center; color: var(--text-muted);">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;margin:0 auto 12px;opacity:0.5;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <div>Preview gambar akan muncul di sini</div>
                </div>
                <canvas id="pdf-canvas" style="display: none; max-width: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 4px;"></canvas>
            </div>
        </div>

    </div>

    <!-- Sertakan library pdf.js dan jszip dari CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script>
        // Setup worker src
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        let pdfDoc = null,
            pageNum = 1,
            pageRendering = false,
            pageNumPending = null,
            scale = 2.0,
            canvas = document.getElementById('pdf-canvas'),
            ctx = canvas.getContext('2d'),
            pdfDataUri = null;

        const fileInput = document.getElementById('pdf-file');
        const renderBtn = document.getElementById('render-btn');
        const downloadBtn = document.getElementById('download-btn');
        const downloadAllBtn = document.getElementById('download-all-btn');
        const downloadMergedBtn = document.getElementById('download-merged-btn');
        const loading = document.getElementById('loading');
        const loadingText = document.getElementById('loading-text');
        const placeholder = document.getElementById('placeholder');
        const pageControls = document.getElementById('page-controls');
        const zipProgress = document.getElementById('zip-progress');

        // Handle File Selection
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type === 'application/pdf') {
                document.getElementById('file-name').innerText = file.name;
                renderBtn.disabled = false;
                
                const fileReader = new FileReader();
                fileReader.onload = function() {
                    const typedarray = new Uint8Array(this.result);
                    loading.style.display = 'block';
                    loadingText.innerText = 'Membaca PDF...';
                    placeholder.style.display = 'none';
                    canvas.style.display = 'none';
                    downloadBtn.style.display = 'none';
                    downloadAllBtn.style.display = 'none';
                    downloadMergedBtn.style.display = 'none';
                    
                    pdfjsLib.getDocument(typedarray).promise.then(pdf => {
                        pdfDoc = pdf;
                        document.getElementById('page-count').textContent = pdf.numPages;
                        pageNum = 1;
                        pageControls.style.display = pdf.numPages > 1 ? 'flex' : 'none';
                        renderPage(pageNum);
                    });
                };
                fileReader.readAsArrayBuffer(file);
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

        // Render Page
        function renderPage(num) {
            pageRendering = true;
            loadingText.innerText = 'Merender Halaman ' + num + '...';
            
            pdfDoc.getPage(num).then(page => {
                scale = parseFloat(document.getElementById('scale').value);
                const viewport = page.getViewport({scale: scale});
                
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                
                const renderTask = page.render(renderContext);
                
                renderTask.promise.then(() => {
                    pageRendering = false;
                    loading.style.display = 'none';
                    canvas.style.display = 'block';
                    
                    if (pageNumPending !== null) {
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    } else {
                        prepareDownload();
                    }
                });
            });
            document.getElementById('page-num').textContent = num;
        }

        // Prepare Download Data
        function prepareDownload() {
            const format = document.querySelector('input[name="format"]:checked').value;
            const extension = format === 'image/jpeg' ? 'jpg' : 'png';
            const quality = format === 'image/jpeg' ? 0.9 : undefined;
            
            // Convert canvas to base64 image
            const imgData = canvas.toDataURL(format, quality);
            
            downloadBtn.href = imgData;
            
            // Set filename based on original PDF name + page number
            let originalName = fileInput.files[0].name.replace('.pdf', '');
            downloadBtn.download = `${originalName}_Page_${pageNum}.${extension}`;
            
            downloadBtn.style.display = 'flex';
            if (pdfDoc.numPages > 1) {
                downloadAllBtn.style.display = 'flex';
                downloadMergedBtn.style.display = 'flex';
            }
        }

        // Events
        document.getElementById('scale').addEventListener('change', () => {
            if (pdfDoc) {
                loading.style.display = 'block';
                canvas.style.display = 'none';
                renderPage(pageNum);
            }
        });

        document.querySelectorAll('input[name="format"]').forEach(radio => {
            radio.addEventListener('change', () => {
                if (pdfDoc && !pageRendering) {
                    prepareDownload();
                }
            });
        });

        renderBtn.addEventListener('click', () => {
            if (pdfDoc) {
                loading.style.display = 'block';
                canvas.style.display = 'none';
                renderPage(pageNum);
            }
        });

        document.getElementById('prev-btn').addEventListener('click', () => {
            if (pageNum <= 1) return;
            pageNum--;
            queueRenderPage(pageNum);
        });

        document.getElementById('next-btn').addEventListener('click', () => {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            queueRenderPage(pageNum);
        });

        function queueRenderPage(num) {
            loading.style.display = 'block';
            canvas.style.display = 'none';
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        // --- DOWNLOAD ALL (ZIP) LOGIC ---
        downloadAllBtn.addEventListener('click', async () => {
            if (!pdfDoc) return;
            
            const format = document.querySelector('input[name="format"]:checked').value;
            const extension = format === 'image/jpeg' ? 'jpg' : 'png';
            const quality = format === 'image/jpeg' ? 0.9 : undefined;
            const originalName = fileInput.files[0].name.replace('.pdf', '');
            
            const zip = new JSZip();
            const folder = zip.folder(originalName);
            
            downloadAllBtn.disabled = true;
            downloadMergedBtn.disabled = true;
            downloadBtn.disabled = true;
            renderBtn.disabled = true;
            pageControls.style.pointerEvents = 'none';
            zipProgress.style.display = 'block';
            
            const tempCanvas = document.createElement('canvas');
            const tempCtx = tempCanvas.getContext('2d');
            const currentScale = parseFloat(document.getElementById('scale').value);

            for (let i = 1; i <= pdfDoc.numPages; i++) {
                zipProgress.innerText = `Memproses halaman ${i} dari ${pdfDoc.numPages}...`;
                
                const page = await pdfDoc.getPage(i);
                const viewport = page.getViewport({scale: currentScale});
                
                tempCanvas.height = viewport.height;
                tempCanvas.width = viewport.width;
                
                await page.render({
                    canvasContext: tempCtx,
                    viewport: viewport
                }).promise;
                
                const imgData = tempCanvas.toDataURL(format, quality);
                const base64Data = imgData.split(',')[1];
                
                folder.file(`${originalName}_Page_${i}.${extension}`, base64Data, {base64: true});
            }
            
            zipProgress.innerText = `Membuat file ZIP...`;
            
            zip.generateAsync({type:"blob"}).then(function(content) {
                const link = document.createElement("a");
                link.href = URL.createObjectURL(content);
                link.download = `${originalName}_All_Pages.zip`;
                link.click();
                
                URL.revokeObjectURL(link.href);
                zipProgress.innerText = `Selesai!`;
                
                setTimeout(() => {
                    zipProgress.style.display = 'none';
                    downloadAllBtn.disabled = false;
                    downloadMergedBtn.disabled = false;
                    downloadBtn.disabled = false;
                    renderBtn.disabled = false;
                    pageControls.style.pointerEvents = 'auto';
                }, 3000);
            });
        });

        // --- DOWNLOAD MERGED (1 IMAGE) LOGIC ---
        downloadMergedBtn.addEventListener('click', async () => {
            if (!pdfDoc) return;
            
            const format = document.querySelector('input[name="format"]:checked').value;
            const extension = format === 'image/jpeg' ? 'jpg' : 'png';
            const quality = format === 'image/jpeg' ? 0.9 : undefined;
            const originalName = fileInput.files[0].name.replace('.pdf', '');
            
            downloadAllBtn.disabled = true;
            downloadMergedBtn.disabled = true;
            downloadBtn.disabled = true;
            renderBtn.disabled = true;
            pageControls.style.pointerEvents = 'none';
            zipProgress.style.display = 'block';
            
            const currentScale = parseFloat(document.getElementById('scale').value);
            
            // 1. Hitung total dimensi (lebar max, tinggi total)
            zipProgress.innerText = `Menghitung dimensi halaman...`;
            let totalHeight = 0;
            let maxWidth = 0;
            const pagesInfo = [];
            
            for (let i = 1; i <= pdfDoc.numPages; i++) {
                const page = await pdfDoc.getPage(i);
                const viewport = page.getViewport({scale: currentScale});
                pagesInfo.push({
                    page: page,
                    viewport: viewport,
                    yOffset: totalHeight
                });
                totalHeight += viewport.height;
                if (viewport.width > maxWidth) {
                    maxWidth = viewport.width;
                }
            }
            
            // 2. Buat master canvas
            const masterCanvas = document.createElement('canvas');
            masterCanvas.width = maxWidth;
            masterCanvas.height = totalHeight;
            const masterCtx = masterCanvas.getContext('2d');
            
            // Beri background putih agar tidak transparan (untuk PNG/JPG)
            masterCtx.fillStyle = '#ffffff';
            masterCtx.fillRect(0, 0, masterCanvas.width, masterCanvas.height);
            
            // 3. Render satu per satu dan tempel ke master
            const tempCanvas = document.createElement('canvas');
            const tempCtx = tempCanvas.getContext('2d');
            
            for (let i = 0; i < pagesInfo.length; i++) {
                zipProgress.innerText = `Menyatukan halaman ${i+1} dari ${pdfDoc.numPages}...`;
                const info = pagesInfo[i];
                
                tempCanvas.width = info.viewport.width;
                tempCanvas.height = info.viewport.height;
                
                await info.page.render({
                    canvasContext: tempCtx,
                    viewport: info.viewport
                }).promise;
                
                // Gambar ke master canvas pada posisi Y (offset) yang tepat
                // Diposisikan ke tengah (horizontal) jika ada halaman yang lebih kecil
                const xOffset = (maxWidth - info.viewport.width) / 2;
                masterCtx.drawImage(tempCanvas, xOffset, info.yOffset);
            }
            
            // 4. Ekspor ke file dan Download
            zipProgress.innerText = `Menyiapkan unduhan gambar gabungan...`;
            
            try {
                const imgData = masterCanvas.toDataURL(format, quality);
                const link = document.createElement("a");
                link.href = imgData;
                link.download = `${originalName}_Merged_All.${extension}`;
                link.click();
                zipProgress.innerText = `Selesai!`;
            } catch (error) {
                zipProgress.innerText = `Gagal! Resolusi terlalu besar untuk digabung (Coba turunkan resolusi).`;
                console.error(error);
            }
            
            setTimeout(() => {
                if(zipProgress.innerText === 'Selesai!') zipProgress.style.display = 'none';
                downloadAllBtn.disabled = false;
                downloadMergedBtn.disabled = false;
                downloadBtn.disabled = false;
                renderBtn.disabled = false;
                pageControls.style.pointerEvents = 'auto';
            }, 4000);
        });
    </script>
</div>
