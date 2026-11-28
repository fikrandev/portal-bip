/**
 * Generator Infografis Observasi Siswa SPMB (PNG Landscape 1920x1080 Full HD via HTML5 Canvas)
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
        fillRoundedRect(ctx, x, y, w, h, 20, '#ffffff', '#e2e8f0', 1.4);
        ctx.restore();

        ctx.textAlign = 'left';
        ctx.fillStyle = '#0f172a';
        ctx.font = '800 20px "Segoe UI", Arial, sans-serif';
        ctx.fillText(title, x + 26, y + 36);

        if (subtitle) {
            ctx.fillStyle = '#64748b';
            ctx.font = '500 13px "Segoe UI", Arial, sans-serif';
            ctx.fillText(fitText(ctx, subtitle, w - 52), x + 26, y + 57);
        }
    }

    function renderInfografisCanvas(data, logoImg) {
        // Exact 1920 x 1080 px (Full HD 16:9 Landscape)
        var W = 1920;
        var H = 1080;
        var pad = 44;
        var contentW = W - (pad * 2); // 1832px

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

        // ── 1. HEADER BANNER (y: 0..146) ────────────────────────────────────────
        var headerH = 146;
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
        ctx.arc(W - 95, 18, 130, 0, Math.PI * 2);
        ctx.fill();
        ctx.beginPath();
        ctx.arc(W - 260, headerH + 15, 90, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();

        // School Logo Box
        fillRoundedRect(ctx, pad, 28, 90, 90, 20, 'rgba(255,255,255,0.96)', 'rgba(255,255,255,0.45)', 1.5);
        if (logoImg) {
            try {
                var ratio = Math.min(72 / logoImg.width, 72 / logoImg.height);
                var dw = logoImg.width * ratio;
                var dh = logoImg.height * ratio;
                ctx.drawImage(logoImg, pad + (90 - dw) / 2, 28 + (90 - dh) / 2, dw, dh);
            } catch (e) {}
        } else {
            ctx.fillStyle = theme.accent;
            ctx.font = '900 24px "Segoe UI", Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(unit, pad + 45, 82);
        }

        // Header Text
        var hx = pad + 112;
        ctx.textAlign = 'left';
        fillRoundedRect(ctx, hx, 25, 330, 26, 13, 'rgba(255,255,255,0.18)', 'rgba(255,255,255,0.30)', 1);
        ctx.fillStyle = '#ffffff';
        ctx.font = '800 12.5px "Segoe UI", Arial, sans-serif';
        ctx.fillText('INFOGRAFIS OBSERVASI CALON SISWA', hx + 16, 43);

        ctx.fillStyle = '#ffffff';
        ctx.font = '900 32px "Segoe UI", Arial, sans-serif';
        ctx.fillText(fitText(ctx, 'UNIT ' + unit + ' — ' + String(data.nama_sekolah || 'SIT BINA INSAN PALU').toUpperCase(), 1180), hx, 86);

        ctx.fillStyle = 'rgba(255, 255, 255, 0.92)';
        ctx.font = '600 16px "Segoe UI", Arial, sans-serif';
        ctx.fillText((data.nama_group || 'SPMB Aktif') + '  •  Total ' + (kpi.total_siswa || 0) + ' Calon Siswa  •  Status Kelengkapan & Capaian Observasi', hx, 115);

        // Right Timestamp Box
        fillRoundedRect(ctx, W - pad - 290, 34, 290, 78, 16, 'rgba(15, 23, 42, 0.34)', 'rgba(255, 255, 255, 0.22)', 1);
        ctx.textAlign = 'right';
        ctx.fillStyle = 'rgba(255, 255, 255, 0.80)';
        ctx.font = '700 12px "Segoe UI", Arial, sans-serif';
        ctx.fillText('DIPERBARUI PADA', W - pad - 22, 60);
        ctx.fillStyle = '#ffffff';
        ctx.font = '800 17.5px "Segoe UI", Arial, sans-serif';
        ctx.fillText(String(data.generated_at || '-'), W - pad - 22, 90);

        // ── 2. 4 KPI CARDS (1x4 ROW, y: 170..294) ──────────────────────────────
        var kpiY = 170;
        var gap = 22;
        var kpiW = (contentW - (gap * 3)) / 4; // 441.5px
        var kpiH = 124;
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
                sub: (kpi.belum_obs_hadir || 0) + ' Hadir blm dinilai  •  ' + (kpi.belum_hadir_qr || 0) + ' Belum QR',
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
            var cx = pad + idx * (kpiW + gap);
            ctx.save();
            ctx.shadowColor = 'rgba(15, 23, 42, 0.05)';
            ctx.shadowBlur = 14;
            ctx.shadowOffsetY = 4;
            fillRoundedRect(ctx, cx, kpiY, kpiW, kpiH, 18, '#ffffff', '#e2e8f0', 1.3);
            ctx.restore();

            fillRoundedRect(ctx, cx + 22, kpiY + 16, 48, 6, 3, card.bar);

            ctx.textAlign = 'left';
            ctx.fillStyle = '#64748b';
            ctx.font = '800 12.5px "Segoe UI", Arial, sans-serif';
            ctx.fillText(card.title, cx + 22, kpiY + 41);

            ctx.fillStyle = card.color;
            ctx.font = '900 40px "Segoe UI", Arial, sans-serif';
            ctx.fillText(card.value, cx + 22, kpiY + 87);

            ctx.fillStyle = '#475569';
            ctx.font = '600 13px "Segoe UI", Arial, sans-serif';
            ctx.fillText(fitText(ctx, card.sub, kpiW - 44), cx + 22, kpiY + 110);
        });

        // ── 3. MAIN 3-COLUMN GRID (y: 318..1028, H=710px) ──────────────────────
        var gridY = 318;
        var gridH = 710;
        var col1W = 576;
        var col2W = 616;
        var col3W = 596;
        var col1X = pad;
        var col2X = col1X + col1W + gap;
        var col3X = col2X + col2W + gap;

        // ── COL 1 TOP: DONUT STATUS OBSERVASI (h = 354px) ──────────────────────
        var donutH = 354;
        drawCardShell(ctx, col1X, gridY, col1W, donutH, 'Status Kelengkapan Observasi', 'Proporsi selesai, sebagian, hadir blm dinilai, & blm QR');

        var donutCx = col1X + 128;
        var donutCy = gridY + 206;
        var outerR = 88;
        var innerR = 54;

        var segs = [
            { label: 'Sudah Selesai', val: kpi.sudah_selesai || 0, color: '#10b981' },
            { label: 'Proses Sebagian', val: kpi.sedang_sebagian || 0, color: '#f59e0b' },
            { label: 'Hadir Blm Dinilai', val: kpi.belum_obs_hadir || 0, color: '#f97316' },
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
        ctx.font = '900 28px "Segoe UI", Arial, sans-serif';
        ctx.fillText(Number(kpi.persen_selesai_total || 0).toFixed(0) + '%', donutCx, donutCy + 5);
        ctx.fillStyle = '#64748b';
        ctx.font = '800 12px "Segoe UI", Arial, sans-serif';
        ctx.fillText('SELESAI', donutCx, donutCy + 23);

        // Donut Legend
        var legX = col1X + 240;
        var legW = col1W - 264;
        var legY = gridY + 92;
        segs.forEach(function (seg, sIdx) {
            var ly = legY + sIdx * 58;
            var pctSeg = totalForDonut > 0 ? ((seg.val / totalForDonut) * 100).toFixed(1) : '0.0';
            fillRoundedRect(ctx, legX, ly, legW, 46, 12, '#f8fafc', '#e2e8f0', 1);
            fillRoundedRect(ctx, legX + 12, ly + 15, 16, 16, 4, seg.color);

            ctx.textAlign = 'left';
            ctx.fillStyle = '#1e293b';
            ctx.font = '700 13.5px "Segoe UI", Arial, sans-serif';
            ctx.fillText(seg.label, legX + 38, ly + 28);

            ctx.textAlign = 'right';
            ctx.fillStyle = '#0f172a';
            ctx.font = '800 14px "Segoe UI", Arial, sans-serif';
            ctx.fillText(seg.val + ' (' + pctSeg + '%)', legX + legW - 14, ly + 28);
        });

        // ── COL 1 BOTTOM: DISTRIBUSI KATEGORI HASIL (h = 334px) ────────────────
        var katY = gridY + donutH + gap;
        var katH = gridH - donutH - gap; // 334px
        drawCardShell(ctx, col1X, katY, col1W, katH, 'Distribusi Kategori Hasil', 'Berdasarkan ' + (kpi.sudah_selesai || 0) + ' siswa selesai penuh');

        if (kategoriList.length === 0 || (kpi.sudah_selesai || 0) === 0) {
            ctx.textAlign = 'center';
            ctx.fillStyle = '#94a3b8';
            ctx.font = '600 14px "Segoe UI", Arial, sans-serif';
            ctx.fillText('Belum ada data kategori siswa yang selesai penuh.', col1X + col1W / 2, katY + 195);
        } else {
            var katStartY = katY + 86;
            kategoriList.forEach(function (kat, kIdx) {
                var ky = katStartY + kIdx * 58;
                var bx = col1X + 26;
                var bw = col1W - 52;

                ctx.textAlign = 'left';
                ctx.fillStyle = '#1e293b';
                ctx.font = '700 14px "Segoe UI", Arial, sans-serif';
                ctx.fillText(fitText(ctx, kat.label, bw - 145), bx, ky + 14);

                ctx.textAlign = 'right';
                ctx.fillStyle = '#0f172a';
                ctx.font = '800 14px "Segoe UI", Arial, sans-serif';
                ctx.fillText(kat.count + ' siswa (' + Number(kat.persen || 0).toFixed(1) + '%)', bx + bw, ky + 14);

                fillRoundedRect(ctx, bx, ky + 23, bw, 16, 8, '#f1f5f9');
                var fillW = Math.max(kat.count > 0 ? 12 : 0, (bw * Math.min(100, Number(kat.persen || 0))) / 100);
                if (fillW > 0) {
                    fillRoundedRect(ctx, bx, ky + 23, fillW, 16, 8, kat.color || '#3b82f6');
                }
            });
        }

        // ── COL 2: PROGRESS PER SESI / RUANGAN (h = 710px) ─────────────────────
        drawCardShell(ctx, col2X, gridY, col2W, gridH, 'Progress Observasi Per Sesi / Ruangan', 'Hijau: Selesai • Kuning: Sebagian • Oranye: Hadir Blm Dinilai • Abu: Blm QR');

        var displaySesi = sesiList.slice();
        if (displaySesi.length > 11) {
            var topTen = displaySesi.slice(0, 10);
            var restSesi = displaySesi.slice(10);
            var agg = { label: '+ ' + restSesi.length + ' Sesi Lainnya', sublabel: 'Gabungan sisa sesi', total: 0, selesai: 0, sebagian: 0, belum_obs_hadir: 0, belum_hadir_qr: 0 };
            restSesi.forEach(function (r) {
                agg.total += (r.total || 0);
                agg.selesai += (r.selesai || 0);
                agg.sebagian += (r.sebagian || 0);
                agg.belum_obs_hadir += (r.belum_obs_hadir || 0);
                agg.belum_hadir_qr += (r.belum_hadir_qr || 0);
            });
            agg.persen_selesai = agg.total > 0 ? ((agg.selesai / agg.total) * 100) : 0;
            topTen.push(agg);
            displaySesi = topTen;
        }

        if (displaySesi.length === 0) {
            ctx.textAlign = 'center';
            ctx.fillStyle = '#94a3b8';
            ctx.font = '600 15px "Segoe UI", Arial, sans-serif';
            ctx.fillText('Belum ada peserta terdaftar pada sesi unit ini.', col2X + col2W / 2, gridY + 355);
        } else {
            var sesiStartY = gridY + 86;
            var sesiRowStep = displaySesi.length <= 8 ? 72 : 54;
            displaySesi.forEach(function (sesi, idx) {
                var sy = sesiStartY + idx * sesiRowStep;
                var sx = col2X + 26;
                var sw = col2W - 52;

                ctx.textAlign = 'left';
                ctx.fillStyle = '#0f172a';
                ctx.font = '700 14px "Segoe UI", Arial, sans-serif';
                var titleLine = sesi.label + (sesi.sublabel ? ' (' + sesi.sublabel + ')' : '');
                ctx.fillText(fitText(ctx, titleLine, sw - 165), sx, sy + 14);

                ctx.textAlign = 'right';
                ctx.fillStyle = '#059669';
                ctx.font = '800 14px "Segoe UI", Arial, sans-serif';
                ctx.fillText(sesi.selesai + '/' + sesi.total + ' (' + Number(sesi.persen_selesai || 0).toFixed(0) + '%)', sx + sw, sy + 14);

                var barH = displaySesi.length <= 8 ? 18 : 14;
                fillRoundedRect(ctx, sx, sy + 23, sw, barH, barH / 2, '#e2e8f0');
                var t = Math.max(1, sesi.total || 0);
                var segList = [
                    { v: sesi.selesai || 0, c: '#10b981' },
                    { v: sesi.sebagian || 0, c: '#f59e0b' },
                    { v: sesi.belum_obs_hadir || 0, c: '#f97316' },
                    { v: sesi.belum_hadir_qr || 0, c: '#cbd5e1' }
                ];
                var curX = sx;
                ctx.save();
                roundedRectPath(ctx, sx, sy + 23, sw, barH, barH / 2);
                ctx.clip();
                segList.forEach(function (sg) {
                    if (sg.v <= 0) return;
                    var wSeg = (sg.v / t) * sw;
                    ctx.fillStyle = sg.c;
                    ctx.fillRect(curX, sy + 23, wSeg + 0.5, barH);
                    curX += wSeg;
                });
                ctx.restore();
            });
        }

        // ── COL 3: RATA-RATA CAPAIAN PER ASPEK PENILAIAN (h = 710px) ───────────
        drawCardShell(ctx, col3X, gridY, col3W, gridH, 'Rata-rata Capaian Per Aspek', 'Persentase rata-rata nilai dari seluruh jawaban observasi masuk');

        var visibleAspek = aspekList.slice(0, 11);
        if (visibleAspek.length === 0) {
            ctx.textAlign = 'center';
            ctx.fillStyle = '#94a3b8';
            ctx.font = '600 15px "Segoe UI", Arial, sans-serif';
            ctx.fillText('Belum ada jawaban aspek penilaian yang tersimpan.', col3X + col3W / 2, gridY + 355);
        } else {
            var aspStartY = gridY + 86;
            var aspRowStep = visibleAspek.length <= 8 ? 72 : 54;
            visibleAspek.forEach(function (asp, idx) {
                var ay = aspStartY + idx * aspRowStep;
                var ax = col3X + 26;
                var aw = col3W - 52;
                var pctA = Math.min(100, Math.max(0, Number(asp.persen || 0)));
                var barColor = pctA >= 80 ? '#10b981' : (pctA >= 65 ? '#3b82f6' : '#f59e0b');

                ctx.textAlign = 'left';
                ctx.fillStyle = '#0f172a';
                ctx.font = '700 14.5px "Segoe UI", Arial, sans-serif';
                ctx.fillText(fitText(ctx, asp.aspek, aw - 90), ax, ay + 14);

                ctx.textAlign = 'right';
                ctx.fillStyle = barColor;
                ctx.font = '900 15.5px "Segoe UI", Arial, sans-serif';
                ctx.fillText(pctA.toFixed(1) + '%', ax + aw, ay + 14);

                var barH = visibleAspek.length <= 8 ? 18 : 14;
                fillRoundedRect(ctx, ax, ay + 23, aw, barH, barH / 2, '#f1f5f9');
                var wFill = (pctA / 100) * aw;
                if (wFill > 0) {
                    fillRoundedRect(ctx, ax, ay + 23, Math.max(12, wFill), barH, barH / 2, barColor);
                }
            });
        }

        // ── 4. FOOTER (y: 1044..1072) ───────────────────────────────────────────
        ctx.textAlign = 'left';
        ctx.fillStyle = '#475569';
        ctx.font = '700 14px "Segoe UI", Arial, sans-serif';
        var leftFoot = data.kepala_sekolah
            ? ('Kepala Sekolah Unit ' + unit + ': ' + data.kepala_sekolah)
            : ('Unit ' + unit + ' — ' + (data.nama_sekolah || 'SIT Bina Insan Palu'));
        ctx.fillText(fitText(ctx, leftFoot, 980), pad, 1060);

        ctx.textAlign = 'right';
        ctx.fillStyle = '#64748b';
        ctx.font = '600 14px "Segoe UI", Arial, sans-serif';
        ctx.fillText('Generated by Portal BIP  •  SPMB Observasi Siswa  •  1920x1080', W - pad, 1060);

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
