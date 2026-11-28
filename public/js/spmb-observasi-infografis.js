/**
 * Generator Infografis Observasi Siswa SPMB (PNG Portrait 1080x1920 via HTML5 Canvas)
 * Portal BIP — SIT Bina Insan Palu
 */
(function (window, document) {
    'use strict';

    var UNIT_THEMES = {
        PAUD: { gradA: '#d97706', gradB: '#ea580c', accent: '#d97706' },
        SD:   { gradA: '#059669', gradB: '#0f766e', accent: '#059669' },
        SMP:  { gradA: '#2563eb', gradB: '#4338ca', accent: '#2563eb' },
        SMA:  { gradA: '#7c3aed', gradB: '#5b21b6', accent: '#7c3aed' }
    };

    var DEFAULT_THEME = UNIT_THEMES.SD;

    function roundedRectPath(ctx, x, y, w, h, r) {
        var radius = Math.min(r, w / 2, h / 2);
        if (radius < 0) radius = 0;
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.arcTo(x + w, y, x + w, y + h, radius);
        ctx.arcTo(x + w, y + h, x, y + h, radius);
        ctx.arcTo(x, y + h, x, y, radius);
        ctx.arcTo(x, y, x + w, y, radius);
        ctx.closePath();
    }

    function fillRoundedRect(ctx, x, y, w, h, r, fillStyle, strokeStyle, lineWidth) {
        roundedRectPath(ctx, x, y, w, h, r);
        if (fillStyle) {
            ctx.fillStyle = fillStyle;
            ctx.fill();
        }
        if (strokeStyle) {
            ctx.strokeStyle = strokeStyle;
            ctx.lineWidth = lineWidth || 1;
            ctx.stroke();
        }
    }

    function fitText(ctx, text, maxWidth) {
        var str = String(text || '');
        if (ctx.measureText(str).width <= maxWidth) return str;
        var ell = '...';
        var len = str.length;
        while (len > 0 && ctx.measureText(str.slice(0, len) + ell).width > maxWidth) {
            len--;
        }
        return str.slice(0, len) + ell;
    }

    function loadLogoImage(url) {
        return new Promise(function (resolve) {
            if (!url) {
                resolve(null);
                return;
            }
            var img = new Image();
            var settled = false;
            var timer = setTimeout(function () {
                if (!settled) {
                    settled = true;
                    resolve(null);
                }
            }, 2200);

            img.onload = function () {
                if (!settled) {
                    settled = true;
                    clearTimeout(timer);
                    resolve(img);
                }
            };
            img.onerror = function () {
                if (!settled) {
                    settled = true;
                    clearTimeout(timer);
                    resolve(null);
                }
            };
            try {
                var u = new URL(url, window.location.origin);
                if (u.origin !== window.location.origin) {
                    img.crossOrigin = 'anonymous';
                }
            } catch (e) {}
            img.src = url;
        });
    }

    function drawCardShell(ctx, x, y, w, h, title, subtitle) {
        ctx.save();
        ctx.shadowColor = 'rgba(15, 23, 42, 0.06)';
        ctx.shadowBlur = 16;
        ctx.shadowOffsetY = 5;
        fillRoundedRect(ctx, x, y, w, h, 22, '#ffffff', '#e2e8f0', 1.4);
        ctx.restore();

        ctx.textAlign = 'left';
        ctx.fillStyle = '#0f172a';
        ctx.font = '800 20px "Segoe UI", Arial, sans-serif';
        ctx.fillText(title, x + 26, y + 38);

        if (subtitle) {
            ctx.fillStyle = '#64748b';
            ctx.font = '500 13.5px "Segoe UI", Arial, sans-serif';
            ctx.fillText(fitText(ctx, subtitle, w - 52), x + 26, y + 60);
        }
    }

    function renderInfografisCanvas(data, logoImg) {
        // Exact 1080 x 1920 px (9:16 Portrait)
        var W = 1080;
        var H = 1920;
        var pad = 40;
        var contentW = W - (pad * 2); // 1000px

        var kpi = data.kpi || {};
        var kategoriList = Array.isArray(data.kategori_list) ? data.kategori_list : [];
        var sesiList = Array.isArray(data.sesi_list) ? data.sesi_list : [];
        var aspekList = Array.isArray(data.aspek_list) ? data.aspek_list : [];
        var unit = String(data.jenjang || 'SD').toUpperCase();
        var theme = UNIT_THEMES[unit] || DEFAULT_THEME;

        var canvas = document.createElement('canvas');
        canvas.width = W;
        canvas.height = H;
        var ctx = canvas.getContext('2d');

        // Background
        ctx.fillStyle = '#f1f5f9';
        ctx.fillRect(0, 0, W, H);

        // ── 1. HEADER BANNER (y: 0..220) ────────────────────────────────────────
        var headerH = 220;
        var grad = ctx.createLinearGradient(0, 0, W, headerH);
        grad.addColorStop(0, '#0f172a');
        grad.addColorStop(0.55, theme.gradA);
        grad.addColorStop(1, theme.gradB);
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, W, headerH);

        // Decorative translucent circles
        ctx.save();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.08)';
        ctx.beginPath();
        ctx.arc(W - 70, 30, 145, 0, Math.PI * 2);
        ctx.fill();
        ctx.beginPath();
        ctx.arc(W - 220, headerH + 20, 100, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();

        // School Logo Box
        fillRoundedRect(ctx, pad, 42, 98, 98, 22, 'rgba(255,255,255,0.96)', 'rgba(255,255,255,0.45)', 1.5);
        if (logoImg) {
            try {
                var ratio = Math.min(78 / logoImg.width, 78 / logoImg.height);
                var dw = logoImg.width * ratio;
                var dh = logoImg.height * ratio;
                ctx.drawImage(logoImg, pad + (98 - dw) / 2, 42 + (98 - dh) / 2, dw, dh);
            } catch (e) {}
        } else {
            ctx.fillStyle = theme.accent;
            ctx.font = '900 26px "Segoe UI", Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(unit, pad + 49, 100);
        }

        // Header Titles
        var hx = pad + 118;
        ctx.textAlign = 'left';
        fillRoundedRect(ctx, hx, 40, 332, 28, 14, 'rgba(255,255,255,0.18)', 'rgba(255,255,255,0.3)', 1);
        ctx.fillStyle = '#ffffff';
        ctx.font = '800 13px "Segoe UI", Arial, sans-serif';
        ctx.fillText('INFOGRAFIS OBSERVASI CALON SISWA', hx + 16, 59);

        ctx.fillStyle = '#ffffff';
        ctx.font = '900 32px "Segoe UI", Arial, sans-serif';
        ctx.fillText(fitText(ctx, 'UNIT ' + unit + ' — ' + String(data.nama_sekolah || 'SIT BINA INSAN PALU').toUpperCase(), W - hx - pad), hx, 104);

        ctx.fillStyle = 'rgba(255, 255, 255, 0.92)';
        ctx.font = '600 16px "Segoe UI", Arial, sans-serif';
        ctx.fillText(fitText(ctx, (data.nama_group || 'SPMB Aktif') + '  •  Total ' + (kpi.total_siswa || 0) + ' Calon Siswa', W - hx - pad), hx, 134);

        // Timestamp pill bar at bottom of banner
        fillRoundedRect(ctx, pad, 160, contentW, 40, 12, 'rgba(15, 23, 42, 0.30)', 'rgba(255,255,255,0.18)', 1);
        ctx.textAlign = 'left';
        ctx.fillStyle = 'rgba(255,255,255,0.88)';
        ctx.font = '700 13.5px "Segoe UI", Arial, sans-serif';
        ctx.fillText('Status Kelengkapan & Capaian Observasi Unit ' + unit, pad + 18, 185);
        ctx.textAlign = 'right';
        ctx.fillStyle = '#ffffff';
        ctx.font = '800 13.5px "Segoe UI", Arial, sans-serif';
        ctx.fillText('Update: ' + String(data.generated_at || '-'), W - pad - 18, 185);

        // ── 2. 4 KPI CARDS (2x2 GRID, y: 244..500) ──────────────────────────────
        var kpiY = 244;
        var kpiGap = 20;
        var kpiW = (contentW - kpiGap) / 2; // 490px
        var kpiH = 118;
        var kpiCards = [
            {
                title: 'TOTAL CALON SISWA',
                value: String(kpi.total_siswa || 0),
                sub: (kpi.hadir_qr || 0) + ' Hadir QR  •  ' + (kpi.belum_hadir_qr || 0) + ' Belum QR',
                color: '#0f172a',
                bar: theme.accent
            },
            {
                title: 'SUDAH OBSERVASI (SELESAI)',
                value: String(kpi.sudah_selesai || 0),
                sub: Number(kpi.persen_selesai_total || 0).toFixed(1) + '% dari total' + ((kpi.sedang_sebagian || 0) > 0 ? ('  (+' + kpi.sedang_sebagian + ' sebagian)') : ''),
                color: '#059669',
                bar: '#10b981'
            },
            {
                title: 'BELUM OBSERVASI',
                value: String(kpi.belum_observasi_total || 0),
                sub: (kpi.belum_obs_hadir || 0) + ' Hadir belum dinilai  •  ' + (kpi.belum_hadir_qr || 0) + ' Belum QR',
                color: '#d97706',
                bar: '#f59e0b'
            },
            {
                title: 'RATA-RATA SKOR OBSERVASI',
                value: Number(kpi.rata_persen || 0).toFixed(1) + '%',
                sub: 'Dari ' + (kpi.sudah_selesai || 0) + ' siswa selesai penuh',
                color: '#2563eb',
                bar: '#3b82f6'
            }
        ];

        kpiCards.forEach(function (card, idx) {
            var col = idx % 2;
            var row = Math.floor(idx / 2);
            var cx = pad + col * (kpiW + kpiGap);
            var cy = kpiY + row * (kpiH + kpiGap);

            ctx.save();
            ctx.shadowColor = 'rgba(15, 23, 42, 0.05)';
            ctx.shadowBlur = 14;
            ctx.shadowOffsetY = 4;
            fillRoundedRect(ctx, cx, cy, kpiW, kpiH, 18, '#ffffff', '#e2e8f0', 1.3);
            ctx.restore();

            fillRoundedRect(ctx, cx + 20, cy + 16, 48, 6, 3, card.bar);

            ctx.textAlign = 'left';
            ctx.fillStyle = '#64748b';
            ctx.font = '800 12.5px "Segoe UI", Arial, sans-serif';
            ctx.fillText(card.title, cx + 20, cy + 40);

            ctx.fillStyle = card.color;
            ctx.font = '900 38px "Segoe UI", Arial, sans-serif';
            ctx.fillText(card.value, cx + 20, cy + 84);

            ctx.fillStyle = '#475569';
            ctx.font = '600 13px "Segoe UI", Arial, sans-serif';
            ctx.fillText(fitText(ctx, card.sub, kpiW - 40), cx + 20, cy + 106);
        });

        // ── 3. CARD DONUT: STATUS KELENGKAPAN OBSERVASI (y: 522..846) ──────────
        var donutCardY = 522;
        var donutCardH = 324;
        drawCardShell(ctx, pad, donutCardY, contentW, donutCardH, 'Status Kelengkapan Observasi Siswa', 'Perbandingan siswa yang sudah selesai, sebagian, hadir belum dinilai, dan belum scan QR');

        var donutCx = pad + 175;
        var donutCy = donutCardY + 194;
        var outerR = 96;
        var innerR = 60;

        var segs = [
            { label: 'Sudah Selesai Observasi', val: kpi.sudah_selesai || 0, color: '#10b981' },
            { label: 'Proses / Sebagian', val: kpi.sedang_sebagian || 0, color: '#f59e0b' },
            { label: 'Hadir QR, Belum Dinilai', val: kpi.belum_obs_hadir || 0, color: '#f97316' },
            { label: 'Belum Scan QR', val: kpi.belum_hadir_qr || 0, color: '#cbd5e1' }
        ];
        var totalForDonut = segs.reduce(function (acc, s) { return acc + s.val; }, 0);

        if (totalForDonut <= 0) {
            ctx.beginPath();
            ctx.arc(donutCx, donutCy, (outerR + innerR) / 2, 0, Math.PI * 2);
            ctx.lineWidth = outerR - innerR;
            ctx.strokeStyle = '#e2e8f0';
            ctx.stroke();
        } else {
            var startAngle = -Math.PI / 2;
            segs.forEach(function (seg) {
                if (seg.val <= 0) return;
                var slice = (seg.val / totalForDonut) * Math.PI * 2;
                ctx.beginPath();
                ctx.arc(donutCx, donutCy, (outerR + innerR) / 2, startAngle, startAngle + slice);
                ctx.lineWidth = outerR - innerR;
                ctx.lineCap = 'butt';
                ctx.strokeStyle = seg.color;
                ctx.stroke();
                startAngle += slice;
            });
        }

        ctx.textAlign = 'center';
        ctx.fillStyle = '#0f172a';
        ctx.font = '900 32px "Segoe UI", Arial, sans-serif';
        ctx.fillText(Number(kpi.persen_selesai_total || 0).toFixed(0) + '%', donutCx, donutCy + 6);
        ctx.fillStyle = '#64748b';
        ctx.font = '800 13px "Segoe UI", Arial, sans-serif';
        ctx.fillText('SELESAI', donutCx, donutCy + 26);

        // Legend on right side
        var legX = pad + 335;
        var legW = contentW - 361;
        var legStartY = donutCardY + 90;
        segs.forEach(function (seg, sIdx) {
            var ly = legStartY + sIdx * 54;
            var pctSeg = totalForDonut > 0 ? ((seg.val / totalForDonut) * 100).toFixed(1) : '0.0';
            fillRoundedRect(ctx, legX, ly, legW, 44, 12, '#f8fafc', '#e2e8f0', 1);
            fillRoundedRect(ctx, legX + 14, ly + 13, 18, 18, 5, seg.color);

            ctx.textAlign = 'left';
            ctx.fillStyle = '#1e293b';
            ctx.font = '700 15px "Segoe UI", Arial, sans-serif';
            ctx.fillText(seg.label, legX + 44, ly + 28);

            ctx.textAlign = 'right';
            ctx.fillStyle = '#0f172a';
            ctx.font = '800 15.5px "Segoe UI", Arial, sans-serif';
            ctx.fillText(seg.val + ' siswa (' + pctSeg + '%)', legX + legW - 16, ly + 28);
        });

        // ── 4. CARD KATEGORI KELAYAKAN (y: 868..1156) ──────────────────────────
        var katCardY = 868;
        var katCardH = 288;
        drawCardShell(ctx, pad, katCardY, contentW, katCardH, 'Distribusi Kategori Hasil Observasi', 'Berdasarkan ' + (kpi.sudah_selesai || 0) + ' siswa yang telah menyelesaikan seluruh pertanyaan observasi');

        if (kategoriList.length === 0 || (kpi.sudah_selesai || 0) === 0) {
            ctx.textAlign = 'center';
            ctx.fillStyle = '#94a3b8';
            ctx.font = '600 15px "Segoe UI", Arial, sans-serif';
            ctx.fillText('Belum ada data kategori siswa yang selesai penuh.', pad + contentW / 2, katCardY + 178);
        } else {
            var katStartY = katCardY + 86;
            kategoriList.forEach(function (kat, kIdx) {
                var ky = katStartY + kIdx * 48;
                var bx = pad + 26;
                var bw = contentW - 52;

                ctx.textAlign = 'left';
                ctx.fillStyle = '#1e293b';
                ctx.font = '700 14.5px "Segoe UI", Arial, sans-serif';
                ctx.fillText(kat.label, bx, ky + 14);

                ctx.textAlign = 'right';
                ctx.fillStyle = '#0f172a';
                ctx.font = '800 14.5px "Segoe UI", Arial, sans-serif';
                ctx.fillText(kat.count + ' siswa (' + Number(kat.persen || 0).toFixed(1) + '%)', bx + bw, ky + 14);

                fillRoundedRect(ctx, bx, ky + 22, bw, 15, 7.5, '#f1f5f9');
                var fillW = Math.max(kat.count > 0 ? 12 : 0, (bw * Math.min(100, Number(kat.persen || 0))) / 100);
                if (fillW > 0) {
                    fillRoundedRect(ctx, bx, ky + 22, fillW, 15, 7.5, kat.color || '#3b82f6');
                }
            });
        }

        // ── 5. CARD PROGRESS PER SESI / RUANGAN (y: 1178..1512) ─────────────────
        var sesiCardY = 1178;
        var sesiCardH = 334;
        drawCardShell(ctx, pad, sesiCardY, contentW, sesiCardH, 'Progress Observasi Per Sesi / Ruangan', 'Hijau: Selesai  •  Kuning: Sebagian  •  Oranye: Hadir Belum Dinilai  •  Abu: Belum QR');

        var displaySesi = sesiList.slice();
        if (displaySesi.length > 5) {
            var topFive = displaySesi.slice(0, 4);
            var rest = displaySesi.slice(4);
            var agg = { label: '+ ' + rest.length + ' Sesi Lainnya', sublabel: 'Gabungan sisa sesi', total: 0, selesai: 0, sebagian: 0, belum_obs_hadir: 0, belum_hadir_qr: 0 };
            rest.forEach(function (r) {
                agg.total += (r.total || 0);
                agg.selesai += (r.selesai || 0);
                agg.sebagian += (r.sebagian || 0);
                agg.belum_obs_hadir += (r.belum_obs_hadir || 0);
                agg.belum_hadir_qr += (r.belum_hadir_qr || 0);
            });
            agg.persen_selesai = agg.total > 0 ? ((agg.selesai / agg.total) * 100) : 0;
            topFive.push(agg);
            displaySesi = topFive;
        }

        if (displaySesi.length === 0) {
            ctx.textAlign = 'center';
            ctx.fillStyle = '#94a3b8';
            ctx.font = '600 15px "Segoe UI", Arial, sans-serif';
            ctx.fillText('Belum ada peserta terdaftar pada sesi unit ini.', pad + contentW / 2, sesiCardY + 195);
        } else {
            var sesiStartY = sesiCardY + 86;
            displaySesi.forEach(function (sesi, idx) {
                var sy = sesiStartY + idx * 48;
                var sx = pad + 26;
                var sw = contentW - 52;

                ctx.textAlign = 'left';
                ctx.fillStyle = '#0f172a';
                ctx.font = '700 14px "Segoe UI", Arial, sans-serif';
                var titleLine = sesi.label + (sesi.sublabel ? '  (' + sesi.sublabel + ')' : '');
                ctx.fillText(fitText(ctx, titleLine, sw - 210), sx, sy + 14);

                ctx.textAlign = 'right';
                ctx.fillStyle = '#059669';
                ctx.font = '800 14px "Segoe UI", Arial, sans-serif';
                ctx.fillText(sesi.selesai + '/' + sesi.total + ' selesai (' + Number(sesi.persen_selesai || 0).toFixed(0) + '%)', sx + sw, sy + 14);

                fillRoundedRect(ctx, sx, sy + 22, sw, 14, 7, '#e2e8f0');
                var t = Math.max(1, sesi.total || 0);
                var segList = [
                    { v: sesi.selesai || 0, c: '#10b981' },
                    { v: sesi.sebagian || 0, c: '#f59e0b' },
                    { v: sesi.belum_obs_hadir || 0, c: '#f97316' },
                    { v: sesi.belum_hadir_qr || 0, c: '#cbd5e1' }
                ];
                var curX = sx;
                ctx.save();
                roundedRectPath(ctx, sx, sy + 22, sw, 14, 7);
                ctx.clip();
                segList.forEach(function (sg) {
                    if (sg.v <= 0) return;
                    var wSeg = (sg.v / t) * sw;
                    ctx.fillStyle = sg.c;
                    ctx.fillRect(curX, sy + 22, wSeg + 0.5, 14);
                    curX += wSeg;
                });
                ctx.restore();
            });
        }

        // ── 6. CARD RATA-RATA CAPAIAN PER ASPEK (y: 1534..1846) ─────────────────
        var aspCardY = 1534;
        var aspCardH = 312;
        drawCardShell(ctx, pad, aspCardY, contentW, aspCardH, 'Rata-rata Capaian Per Aspek Penilaian', 'Persentase rata-rata nilai dari seluruh jawaban observasi yang telah masuk');

        var visibleAspek = aspekList.slice(0, 10);
        if (visibleAspek.length === 0) {
            ctx.textAlign = 'center';
            ctx.fillStyle = '#94a3b8';
            ctx.font = '600 15px "Segoe UI", Arial, sans-serif';
            ctx.fillText('Belum ada jawaban aspek penilaian yang tersimpan.', pad + contentW / 2, aspCardY + 185);
        } else {
            var useTwoCols = visibleAspek.length > 5;
            var aColGap = 32;
            var aColW = useTwoCols ? ((contentW - 52 - aColGap) / 2) : (contentW - 52);
            var aStartY = aspCardY + 86;
            var rowStep = 44;

            visibleAspek.forEach(function (asp, idx) {
                var colIdx = useTwoCols ? Math.floor(idx / 5) : 0;
                var rowIdx = useTwoCols ? (idx % 5) : idx;
                var ax = pad + 26 + colIdx * (aColW + aColGap);
                var ay = aStartY + rowIdx * rowStep;

                var pctA = Math.min(100, Math.max(0, Number(asp.persen || 0)));
                var barColor = pctA >= 80 ? '#10b981' : (pctA >= 65 ? '#3b82f6' : '#f59e0b');

                ctx.textAlign = 'left';
                ctx.fillStyle = '#0f172a';
                ctx.font = '700 13.5px "Segoe UI", Arial, sans-serif';
                ctx.fillText(fitText(ctx, asp.aspek, aColW - 76), ax, ay + 13);

                ctx.textAlign = 'right';
                ctx.fillStyle = barColor;
                ctx.font = '900 14px "Segoe UI", Arial, sans-serif';
                ctx.fillText(pctA.toFixed(1) + '%', ax + aColW, ay + 13);

                fillRoundedRect(ctx, ax, ay + 20, aColW, 13, 6.5, '#f1f5f9');
                var wFill = (pctA / 100) * aColW;
                if (wFill > 0) {
                    fillRoundedRect(ctx, ax, ay + 20, Math.max(10, wFill), 13, 6.5, barColor);
                }
            });
        }

        // ── 7. FOOTER (y: 1862..1910) ───────────────────────────────────────────
        ctx.textAlign = 'left';
        ctx.fillStyle = '#475569';
        ctx.font = '700 13.5px "Segoe UI", Arial, sans-serif';
        var leftFoot = data.kepala_sekolah
            ? ('Kepala Sekolah Unit ' + unit + ': ' + data.kepala_sekolah)
            : ('Unit ' + unit + ' — ' + (data.nama_sekolah || 'SIT Bina Insan Palu'));
        ctx.fillText(fitText(ctx, leftFoot, 580), pad, 1890);

        ctx.textAlign = 'right';
        ctx.fillStyle = '#64748b';
        ctx.font = '600 13.5px "Segoe UI", Arial, sans-serif';
        ctx.fillText('Generated by Portal BIP  •  SPMB Observasi Siswa', W - pad, 1890);

        return canvas;
    }

    function triggerCanvasDownload(canvas, filename) {
        return new Promise(function (resolve, reject) {
            if (typeof canvas.toBlob === 'function') {
                try {
                    canvas.toBlob(function (blob) {
                        if (!blob) {
                            reject(new Error('Gagal membuat file PNG dari canvas.'));
                            return;
                        }
                        var objUrl = URL.createObjectURL(blob);
                        var link = document.createElement('a');
                        link.href = objUrl;
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        setTimeout(function () { URL.revokeObjectURL(objUrl); }, 2500);
                        resolve();
                    }, 'image/png');
                    return;
                } catch (err) {
                    reject(err);
                    return;
                }
            }
            try {
                var dataUrl = canvas.toDataURL('image/png');
                var link2 = document.createElement('a');
                link2.href = dataUrl;
                link2.download = filename;
                document.body.appendChild(link2);
                link2.click();
                document.body.removeChild(link2);
                resolve();
            } catch (err2) {
                reject(err2);
            }
        });
    }

    window.downloadObservasiInfografis = async function (apiUrl, jenjang, btnEl) {
        var origHtml = btnEl ? btnEl.innerHTML : '';
        if (btnEl) {
            btnEl.disabled = true;
            btnEl.innerHTML = '<span>⏳ Membuat PNG...</span>';
        }

        try {
            var res = await fetch(apiUrl, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            var json = await res.json();
            if (!res.ok || !json || !json.success || !json.data) {
                throw new Error((json && json.message) ? json.message : 'Gagal mengambil data infografis.');
            }

            var data = json.data;
            var logoImg = await loadLogoImage(data.logo_url || '');
            var canvas = renderInfografisCanvas(data, logoImg);
            var safeUnit = String(data.jenjang || jenjang || 'UNIT').replace(/[^A-Za-z0-9_-]/g, '_');
            var filename = 'Infografis_Observasi_' + safeUnit + '_' + (data.generated_ymd || 'latest') + '.png';

            try {
                await triggerCanvasDownload(canvas, filename);
            } catch (taintErr) {
                var cleanCanvas = renderInfografisCanvas(data, null);
                await triggerCanvasDownload(cleanCanvas, filename);
            }
        } catch (err) {
            alert('Gagal mengunduh infografis ' + jenjang + ': ' + err.message);
        } finally {
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.innerHTML = origHtml;
            }
        }
    };
})(window, document);
