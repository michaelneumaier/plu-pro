// ZXing WASM worker for barcode decoding
// Runs decoding off the main thread for performance

import { readBarcodes, prepareZXingModule } from 'zxing-wasm/reader';

/** @typedef {{ tryHarder?: boolean; maxNumberOfSymbols?: number; formats?: string[] }} ReaderOptions */

/** @type {ReaderOptions} */
let readerOptions = {
    tryHarder: true,
    maxNumberOfSymbols: 1,
};

let initialized = false;

async function ensureInitialized() {
    if (initialized) return;
    try {
        // Use default CDN for wasm. Can be customized by sending 'prepare' message later if needed.
        await prepareZXingModule({ fireImmediately: true });
        initialized = true;
        self.postMessage({ type: 'ready' });
    } catch (err) {
        self.postMessage({ type: 'error', error: `ZXing init failed: ${err?.message || err}` });
    }
}

/**
 * Convert an ImageBitmap to ImageData using OffscreenCanvas in worker
 * @param {ImageBitmap} bitmap
 * @returns {ImageData}
 */
function bitmapToImageData(bitmap) {
    const { width, height } = bitmap;
    const canvas = new OffscreenCanvas(width, height);
    const ctx = canvas.getContext('2d', { willReadFrequently: false });
    ctx.drawImage(bitmap, 0, 0);
    const imageData = ctx.getImageData(0, 0, width, height);
    bitmap.close();
    return imageData;
}

self.onmessage = async (event) => {
    const { data } = event;
    try {
        switch (data?.type) {
            case 'init': {
                // Optionally accept reader options and prefetch wasm
                if (data?.options && typeof data.options === 'object') {
                    readerOptions = { ...readerOptions, ...data.options };
                }
                await ensureInitialized();
                return;
            }
            case 'prepare': {
                // Allow caller to override wasm locateFile etc. if needed in the future
                await prepareZXingModule({ ...(data?.args || {}), fireImmediately: true });
                initialized = true;
                self.postMessage({ type: 'ready' });
                return;
            }
            case 'decode': {
                await ensureInitialized();
                const bitmap = data.bitmap; // ImageBitmap (transferred)
                if (!bitmap) {
                    self.postMessage({ type: 'result', ok: false });
                    return;
                }
                const imageData = bitmapToImageData(bitmap);
                const t0 = performance.now();
                const results = await readBarcodes(imageData, readerOptions);
                const durationMs = performance.now() - t0;
                if (results && results.length > 0) {
                    // send the first result for speed
                    const r = results[0];
                    self.postMessage({
                        type: 'result',
                        ok: true,
                        text: r.text,
                        format: r.format,
                        durationMs,
                    });
                } else {
                    self.postMessage({ type: 'result', ok: false, durationMs });
                }
                return;
            }
            case 'decodeBlob': {
                await ensureInitialized();
                const blob = data.blob; // Blob or File
                if (!blob) {
                    self.postMessage({ type: 'result', ok: false });
                    return;
                }
                const t0 = performance.now();
                const results = await readBarcodes(blob, readerOptions);
                const durationMs = performance.now() - t0;
                if (results && results.length > 0) {
                    const r = results[0];
                    self.postMessage({
                        type: 'result',
                        ok: true,
                        text: r.text,
                        format: r.format,
                        durationMs,
                    });
                } else {
                    self.postMessage({ type: 'result', ok: false, durationMs });
                }
                return;
            }
            default: {
                // no-op
                return;
            }
        }
    } catch (err) {
        self.postMessage({ type: 'error', error: err?.message || String(err) });
    }
};


