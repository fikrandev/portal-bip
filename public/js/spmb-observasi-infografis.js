/**
 * Generator Infografis Observasi Siswa SPMB (PNG High-DPI via HTML5 Canvas)
 * Portal BIP — SIT Bina Insan Palu
 */
(function (window, document) {
    'use strict';

    var UNIT_THEMES = {
        PAUD: { gradA: '#d97706', gradB: '#ea580c', accent: '#d97706', badgeBg: '#fef3c7', badgeFg: '#92400e' },
        SD:   { gradA: '#059669', gradB: '#0f766e', accent: '#059669', badgeBg: '#d1fae5', badgeFg: '#065f46' },
        SMP:  { gradA: '#2563eb', gradB: '#4338ca', accent: '#2563eb', badgeBg: '#dbeafe', badgeFg: '#1e40af' },
        SMA:  { gradA: '#7c3aed', gradB: '#5b21b6', accent: '#7c3aed', badgeBg: '#ede9fe', badgeFg: '#5b21b6' }
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
        ctx.shadowBlur = 14;
        ctx.shadowOffsetY = 4;
        fillRoundedRect(ctx, x, y, w, h, 18, '#ffffff', '#e2e8f0', 1.2);
        ctx.restore();

        ctx.fillStyle = '#0f172a';
        ctx.font = '800 17px "Segoe UI", Arial, sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(title, x + 22, y + 32);

        if (subtitle) {
            ctx.fillStyle = '#64748b';
            ctx.font = '500 12px "Segoe UI", Arial, sans-serif';
            ctx.fillText(subtitle, x + 22, y + 51);
        }
    }

    function renderInfografisCanvas(data, logoImg) {
        var W = 1200;
        var pad = 36;
        var kpi = data.kpi || {};
        var kategoriList = Array.isArray(data.kategori_list) ? data.kategori_list : [];
        var sesiList = Array.isArray(data.sesi_list) ? data.sesi_list : [];
        var aspekList = Array.isArray(data.aspek_list) ? data.aspek_list : [];
        var unit = String(data.jenjang || 'SD').toUpperCase();
        var theme = UNIT_THEMES[unit] || DEFAULT_THEME;

        var headerH = 158;
        var kpiY = headerH + 24;
        var kpiH = 122;
        var row1Y = kpiY + kpiH + 22;
        var row1H = 310;
        var row2Y = row1Y + row1H + 22;

        var visibleSesi = sesiList.slice(0, 14);
        var visibleAspek = aspekList.slice(0, 12);
        var maxRows = Math.max(Math.max(visibleSesi.length, 1), Math.max(visibleAspek.length, 1));
        var row2H = Math.max(260, 110 + (maxRows * 46));
        var footerY = row2Y + row2H + 18;
        var H = footerY + 46;

        var scale = 2;
        var canvas = document.createElement('canvas');
        canvas.width = W * scale;
        canvas.height = H * scale;

        var ctx = canvas.getContext('2d');
        ctx.scale(scale, scale);

        // Background
        ctx.fillStyle = '#f1f5f9';
        ctx.fillRect(0, 0, W, H);

        // Header Gradient Banner
        var grad = ctx.createLinearGradient(0, 0, W, headerH);
        grad.addColorStop(0, '#0f172a');
        grad.addColorStop(0.55, theme.gradA);
        grad.addColorStop(1, theme.gradB);
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, W, headerH);

        // Subtle decorative circles in banner
        ctx.save();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.08)';
        ctx.beginPath();
        ctx.arc(W - 90, 26, 125, 0, Math.PI * 2);
        ctx.fill();
        ctx.beginPath();
        ctx.arc(W - 220, headerH + 10, 85, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();

        // Logo box
        fillRoundedRect(ctx, pad, 34, 86, 86, 20, 'rgba(255,255,255,0.96)', 'rgba(255,255,255,0.4)', 1.5);
        if (logoImg) {
            try {
                var ratio = Math.min(68 / logoImg.width, 68 / logoImg.height);
                var dw = logoImg.width * ratio;
                var dh = logoImg.height * ratio;
                ctx.drawImage(logoImg, pad + (86 - dw) / 2, 34 + (86 - dh) / 2, dw, dh);
            } catch (e) {}
        } else {
            ctx.fillStyle = theme.accent;
            ctx.font = '900 22px "Segoe UI", Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(unit, pad + 43, 85);
        }

        // Header text
        var hTextX = pad + 106;
        ctx.textAlign = 'left';
        fillRoundedRect(ctx, hTextX, 32, 275, 24, 12, 'rgba(255,255,255,0.18)', 'rgba(255,255,255,0.28)', 1);
        ctx.fillStyle = '#ffffff';
        ctx.font = '800 11px "Segoe UI", Arial, sans-serif';
        ctx.fillText('INFOGRAFIS OBSERVASI CALON SISWA', hTextX + 14, 48);

        ctx.fillStyle = '#ffffff';
        ctx.font = '900 28px "Segoe UI", Arial, sans-serif';
        ctx.fillText(fitText(ctx, 'UNIT ' + unit + ' — ' + String(data.nama_sekolah || 'SIT BINA INSAN PALU').toUpperCase(), 620), hTextX, 88);

        ctx.fillStyle = 'rgba(255, 255, 255, 0.90)';
        ctx.font = '600 14px "Segoe UI", Arial, sans-serif';
        ctx.fillText((data.nama_group || 'SPMB Aktif') + '  •  Total ' + (kpi.total_siswa || 0) + ' Calon Siswa', hTextX, 115);

        // Right banner stamp
        fillRoundedRect(ctx, W - pad - 240, 42, 240, 74, 16, 'rgba(15, 23, 42, 0.34)', 'rgba(255, 255, 255, 0.22)', 1);
        ctx.textAlign = 'right';
        ctx.fillStyle = 'rgba(255, 255, 255, 0.78)';
        ctx.font = '700 11px "Segoe UI", Arial, sans-serif';
        ctx.fillText('DIPERBARUI PADA', W - pad - 18, 66);
        ctx.fillStyle = '#ffffff';
        ctx.font = '800 15px "Segoe UI", Arial, sans-serif';
        ctx.fillText(String(data.generated_at || '-'), W - pad - 18, 91);

        // KPI Cards (4 columns)
        var gap = 18;
        var kpiW = (W - (pad * 2) - (gap * 3)) / 4;
        var kpiCards = [
            {
                title: 'TOTAL CALON SISWA',
                value: String(kpi.total_siswa || 0),
                sub: (kpi.hadir_qr || 0) + ' Hadir QR  •  ' + (kpi.belum_hadir_qr || 0) + ' Belum QR',
                color: '#0f172a',
                bar: theme.accent,
                bgPill: '#f8fafc'
            },
            {
                title: 'SUDAH OBSERVASI',
                value: String(kpi.sudah_selesai || 0),
                sub: (kpi.persen_selesai_total || 0) + '% Selesai' + ((kpi.sedang_sebagian || 0) > 0 ? ('  (+' + kpi.sedang_sebagian + ' sebagian)') : ''),
                color: '#059669',
                bar: '#10b981',
                bgPill: '#ecfdf5'
            },
            {
                title: 'BELUM OBSERVASI',
                value: String(kpi.belum_observasi_total || 0),
                sub: (kpi.belum_obs_hadir || 0) + ' Hadir belum dinilai  •  ' + (kpi.belum_hadir_qr || 0) + ' Blm QR',
                color: '#d97706',
                bar: '#f59e0b',
                bgPill: '#fffbeb'
            },
            {
                title: 'RATA-RATA SKOR',
                value: Number(kpi.rata_persen || 0).toFixed(1) + '%',
                sub: 'Dari ' + (kpi.sudah_selesai || 0) + ' siswa selesai penuh',
                color: '#2563eb',
                bar: '#3b82f6',
                bgPill: '#eff6ff'
            }
        ];

        kpiCards.forEach(function (card, idx) {
            var cx = pad + idx * (kpiW + gap);
            ctx.save();
            ctx.shadowColor = 'rgba(15, 23, 42, 0.05)';
            ctx.shadowBlur = 12;
            ctx.shadowOffsetY = 3;
            fillRoundedRect(ctx, cx, kpiY, kpiW, kpiH, 16, '#ffffff', '#e2e8f0', 1.2);
            ctx.restore();

            fillRoundedRect(ctx, cx + 16, kpiY + 14, 42, 5, 3, card.bar);

            ctx.textAlign = 'left';
            ctx.fillStyle = '#64748b';
            ctx.font = '800 11px "Segoe UI", Arial, sans-serif';
            ctx.fillText(card.title, cx + 16, kpiY + 37);

            ctx.fillStyle = card.color;
            ctx.font = '900 34px "Segoe UI", Arial, sans-serif';
            ctx.fillText(card.value, cx + 16, kpiY + 78);

            ctx.fillStyle = '#475569';
            ctx.font = '600 11.5px "Segoe UI", Arial, sans-serif';
            ctx.fillText(fitText(ctx, card.sub, kpiW - 32), cx + 16, kpiY + 104);
        });

        // Row 1: Two equal cards
        var colW = (W - (pad * 2) - gap) / 2;
        var colRightX = pad + colW + gap;

        // Card 1A: Donut Status Observasi
        drawCardShell(ctx, pad, row1Y, colW, row1H, 'Status Kelengkapan Observasi', 'Perbandingan siswa yang sudah selesai, sebagian, & belum diobservasi');

        var donutCx = pad + 135;
        var donutCy = row1Y + 180;
        var outerR = 82;
        var innerR = 52;

        var segs = [
            { label: 'Sudah Selesai', val: kpi.sudah_selesai || 0, color: '#10b981' },
            { label: 'Proses Sebagian', val: kpi.sedang_sebagian || 0, color: '#f59e0b' },
            { label: 'Hadir Belum Dinilai', val: kpi.belum_obs_hadir || 0, color: '#f97316' },
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

        // Donut center text
        ctx.textAlign = 'center';
        ctx.fillStyle = '#0f172a';
        ctx.font = '900 24px "Segoe UI", Arial, sans-serif';
        ctx.fillText(Number(kpi.persen_selesai_total || 0).toFixed(0) + '%', donutCx, donutCy + 4);
        ctx.fillStyle = '#64748b';
        ctx.font = '700 11px "Segoe UI", Arial, sans-serif';
        ctx.fillText('SELESAI', donutCx, donutCy + 21);

        // Donut legend on right side of card 1A
        var legX = pad + 250;
        var legY = row1Y + 95;
        segs.forEach(function (seg, sIdx) {
            var ly = legY + (sIdx * 48);
            var pctSeg = totalForDonut > 0 ? ((seg.val / totalForDonut) * 100).toFixed(1) : '0.0';
            fillRoundedRect(ctx, legX, ly, colW - 272, 38, 10, '#f8fafc', '#e2e8f0', 1);
            fillRoundedRect(ctx, legX + 10, ly + 12, 14, 14, 4, seg.color);

            ctx.textAlign = 'left';
            ctx.fillStyle = '#334155';
            ctx.font = '700 12px "Segoe UI", Arial, sans-serif';
            ctx.fillText(seg.label, legX + 32, ly + 24);

            ctx.textAlign = 'right';
            ctx.fillStyle = '#0f172a';
            ctx.font = '800 13px "Segoe UI", Arial, sans-serif';
            ctx.fillText(seg.val + ' (' + pctSeg + '%)', legX + colW - 282, ly + 24);
        });

        // Card 1B: Distribusi Kategori Hasil
        drawCardShell(ctx, colRightX, row1Y, colW, row1H, 'Distribusi Kategori Kelayakan', 'Berdasarkan ' + (kpi.sudah_selesai || 0) + ' siswa yang telah selesai diobservasi');

        if (kategoriList.length === 0 || (kpi.sudah_selesai || 0) === 0) {
            ctx.textAlign = 'center';
            ctx.fillStyle = '#94a3b8';
            ctx.font = '600 13px "Segoe UI", Arial, sans-serif';
            ctx.fillText('Belum ada data kategori siswa yang selesai penuh.', colRightX + colW / 2, row1Y + 180);
        } else {
            var katStartY = row1Y + 88;
            kategoriList.forEach(function (kat, kIdx) {
                var ky = katStartY + kIdx * 52;
                var barX = colRightX + 22;
                var barW = colW - 44;

                ctx.textAlign = 'left';
                ctx.fillStyle = '#1e293b';
                ctx.font = '700 12.5px "Segoe UI", Arial, sans-serif';
                ctx.fillText(kat.label, barX, ky + 12);

                ctx.textAlign = 'right';
                ctx.fillStyle = '#0f172a';
                ctx.font = '800 12.5px "Segoe UI", Arial, sans-serif';
                ctx.fillText(kat.count + ' siswa (' + Number(kat.persen || 0).toFixed(1) + '%)', barX + barW, ky + 12);

                fillRoundedRect(ctx, barX, ky + 20, barW, 14, 7, '#f1f5f9');
                var fillW = Math.max(kat.count > 0 ? 10 : 0, (barW * Math.min(100, Number(kat.persen || 0))) / 100);
                if (fillW > 0) {
                    fillRoundedRect(ctx, barX, ky + 20, fillW, 14, 7, kat.color || '#3b82f6');
                }
            });
        }

        // Row 2A: Progress Per Sesi / Ruangan (Stacked Horizontal Bars)
        drawCardShell(ctx, pad, row2Y, colW, row2H, 'Progress Observasi Per Sesi / Ruangan', 'Hijau: Selesai • Kuning: Sebagian • Oranye: Hadir Blm Dinilai • Abu: Blm QR');

        if (visibleSesi.length === 0) {
            ctx.textAlign = 'center';
            ctx.fillStyle = '#94a3b8';
            ctx.font = '600 13px "Segoe UI", Arial, sans-serif';
            ctx.fillText('Belum ada peserta terdaftar pada sesi unit ini.', pad + colW / 2, row2Y + 145);
        } else {
            var sesiY = row2Y + 78;
            visibleSesi.forEach(function (sesi, idx) {
                var sy = sesiY + idx * 46;
                var sx = pad + 22;
                var sw = colW - 44;

                ctx.textAlign = 'left';
                ctx.fillStyle = '#0f172a';
                ctx.font = '700 12px "Segoe UI", Arial, sans-serif';
                var titleLine = sesi.label + (sesi.sublabel ? ' (' + sesi.sublabel + ')' : '');
                ctx.fillText(fitText(ctx, titleLine, sw - 125), sx, sy + 12);

                ctx.textAlign = 'right';
                ctx.fillStyle = '#059669';
                ctx.font = '800 12px "Segoe UI", Arial, sans-serif';
                ctx.fillText(sesi.selesai + '/' + sesi.total + ' selesai (' + Number(sesi.persen_selesai || 0).toFixed(0) + '%)', sx + sw, sy + 12);

                fillRoundedRect(ctx, sx, sy + 19, sw, 13, 6, '#e2e8f0');
                var t = Math.max(1, sesi.total || 0);
                var segList = [
                    { v: sesi.selesai || 0, c: '#10b981' },
                    { v: sesi.sebagian || 0, c: '#f59e0b' },
                    { v: sesi.belum_obs_hadir || 0, c: '#f97316' },
                    { v: sesi.belum_hadir_qr || 0, c: '#cbd5e1' }
                ];
                var curX = sx;
                ctx.save();
                roundedRectPath(ctx, sx, sy + 19, sw, 13, 6);
                ctx.clip();
                segList.forEach(function (sg) {
                    if (sg.v <= 0) return;
                    var wSeg = (sg.v / t) * sw;
                    ctx.fillStyle = sg.c;
                    ctx.fillRect(curX, sy + 19, wSeg + 0.5, 13);
                    curX += wSeg;
                });
                ctx.restore();
            });
        }

        // Row 2B: Capaian Rata-Rata Per Aspek Penilaian
        drawCardShell(ctx, colRightX, row2Y, colW, row2H, 'Rata-rata Capaian Per Aspek Penilaian', 'Persentase rata-rata nilai dari seluruh jawaban yang sudah masuk');

        if (visibleAspek.length === 0) {
            ctx.textAlign = 'center';
            ctx.fillStyle = '#94a3b8';
            ctx.font = '600 13px "Segoe UI", Arial, sans-serif';
            ctx.fillText('Belum ada jawaban aspek penilaian yang tersimpan.', colRightX + colW / 2, row2Y + 145);
        } else {
            var aspY = row2Y + 78;
            visibleAspek.forEach(function (asp, idx) {
                var ay = aspY + idx * 46;
                var ax = colRightX + 22;
                var aw = colW - 44;
                var pctA = Math.min(100, Math.max(0, Number(asp.persen || 0)));
                var barColor = pctA >= 80 ? '#10b981' : (pctA >= 65 ? '#3b82f6' : '#f59e0b');

                ctx.textAlign = 'left';
                ctx.fillStyle = '#0f172a';
                ctx.font = '700 12.5px "Segoe UI", Arial, sans-serif';
                ctx.fillText(fitText(ctx, asp.aspek, aw - 90), ax, ay + 12);

                ctx.textAlign = 'right';
                ctx.fillStyle = barColor;
                ctx.font = '900 13px "Segoe UI", Arial, sans-serif';
                ctx.fillText(pctA.toFixed(1) + '%', ax + aw, ay + 12);

                fillRoundedRect(ctx, ax, ay + 19, aw, 13, 6, '#f1f5f9');
                var wFill = (pctA / 100) * aw;
                if (wFill > 0) {
                    fillRoundedRect(ctx, ax, ay + 19, Math.max(8, wFill), 13, 6, barColor);
                }
            });
        }

        // Footer
        ctx.textAlign = 'left';
        ctx.fillStyle = '#475569';
        ctx.font = '600 12px "Segoe UI", Arial, sans-serif';
        var leftFoot = data.kepala_sekolah
            ? ('Kepala Sekolah Unit ' + unit + ': ' + data.kepala_sekolah)
            : ('Unit ' + unit + ' — ' + (data.nama_sekolah || 'SIT Bina Insan Palu'));
        ctx.fillText(leftFoot, pad, footerY + 12);

        ctx.textAlign = 'right';
        ctx.fillStyle = '#64748b';
        ctx.fillText('Generated by Portal BIP  •  SPMB Observasi Siswa', W - pad, footerY + 12);

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
                // Jika logo eksternal menyebabkan canvas tainted, render ulang tanpa gambar eksternal
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
