/**
 * Dashboard charts (monthly growth + weekly activity).
 *
 * Data comes from the `<script type="application/json" id="homeChartData">`
 * block in admin/home/index.blade.php rather than inline JS, so this file is
 * static and cacheable. The JSON is emitted with HEX_TAG/HEX_AMP/HEX_APOS/
 * HEX_QUOT so no value can break out of the script tag.
 */
document.addEventListener('DOMContentLoaded', function () {
    const dataEl = document.getElementById('homeChartData');
    const monthlyCanvas = document.getElementById('monthlyChart');
    const weeklyCanvas = document.getElementById('weeklyChart');
    if (!dataEl || !monthlyCanvas || !weeklyCanvas || typeof Chart === 'undefined') return;

    let __D;
    try {
        __D = JSON.parse(dataEl.textContent);
    } catch (e) {
        console.error('[dashboard-charts] invalid chart data', e);
        return;
    }

    /* ---------- theme tokens ---------- */
    function tok() {
        const dark = document.documentElement.getAttribute('data-pc-theme') === 'dark'
                  || document.body.dataset.pcTheme === 'dark';
        return {
            grid:    dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.05)',
            tick:    dark ? '#94a3b8' : '#64748b',
            tipBg:   dark ? 'rgba(17,21,37,.96)' : 'rgba(255,255,255,.97)',
            tipBd:   dark ? 'rgba(255,255,255,.12)' : 'rgba(0,0,0,.07)',
            tipTxt:  dark ? '#f8fafc' : '#1e1b4b',
        };
    }

    /* ---------- رسم بياني الشهري ---------- */
    const mLabels  = __D.mLabels;
    const mStudents = __D.mStudents;
    const mTeachers = __D.mTeachers;
    // أسماء السلاسل بتيجي مترجمة من الـ Blade؛ الـ fallback عربي لو الحقل مش موجود
    const mStudentsLabel = __D.mStudentsLabel || 'طلاب جدد';
    const mTeachersLabel = __D.mTeachersLabel || 'معلمون جدد';

    const ctxM = monthlyCanvas.getContext('2d');
    const t = tok();

    const gS = ctxM.createLinearGradient(0, 0, 0, 250);
    gS.addColorStop(0, 'rgba(14,165,233,.32)');
    gS.addColorStop(1, 'rgba(14,165,233,.01)');

    const gT = ctxM.createLinearGradient(0, 0, 0, 250);
    gT.addColorStop(0, 'rgba(168,85,247,.26)');
    gT.addColorStop(1, 'rgba(168,85,247,.01)');

    const mChart = new Chart(ctxM, {
        type: 'line',
        data: {
            labels: mLabels,
            datasets: [
                {
                    label: mStudentsLabel,
                    data: mStudents,
                    borderColor: '#0ea5e9',
                    backgroundColor: gS,
                    borderWidth: 2.5,
                    tension: .42,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0ea5e9',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                },
                {
                    label: mTeachersLabel,
                    data: mTeachers,
                    borderColor: '#a855f7',
                    backgroundColor: gT,
                    borderWidth: 2.5,
                    tension: .42,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#a855f7',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: t.tick,
                        boxWidth: 12, boxHeight: 12,
                        borderRadius: 4, useBorderRadius: true,
                        font: { size: 12, weight: '700' }, padding: 16,
                    }
                },
                tooltip: {
                    backgroundColor: t.tipBg,
                    titleColor: t.tipTxt, bodyColor: t.tipTxt,
                    borderColor: t.tipBd, borderWidth: 1,
                    padding: 14, cornerRadius: 14, boxPadding: 6,
                    usePointStyle: true,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: t.tick, font: { size: 12, weight: '600' } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: t.grid, drawBorder: false },
                    ticks: { color: t.tick, precision: 0, font: { size: 11 } }
                }
            }
        }
    });

    /* ---------- رسم بياني أسبوعي ---------- */
    const wLabels = __D.wLabels;
    const wData   = __D.wData;
    const wStudentsLabel = __D.wStudentsLabel || 'طلاب';

    const ctxW = weeklyCanvas.getContext('2d');
    const gW = ctxW.createLinearGradient(0, 0, 0, 150);
    gW.addColorStop(0, 'rgba(6,182,212,.32)');
    gW.addColorStop(1, 'rgba(139,92,246,.01)');

    const wChart = new Chart(ctxW, {
        type: 'line',
        data: {
            labels: wLabels,
            datasets: [{
                label: wStudentsLabel,
                data: wData,
                borderColor: '#06b6d4',
                backgroundColor: gW,
                borderWidth: 2.5,
                tension: .45,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#8b5cf6',
                pointBorderWidth: 2,
                pointRadius: 3.5,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: t.tipBg,
                    titleColor: t.tipTxt, bodyColor: t.tipTxt,
                    borderColor: t.tipBd, borderWidth: 1,
                    padding: 12, cornerRadius: 12,
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: t.tick, font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: t.grid }, ticks: { color: t.tick, precision: 0, font: { size: 10 } } }
            }
        }
    });

    /* ---------- theme observer ---------- */
    const obs = new MutationObserver(() => {
        const nt = tok();
        [mChart, wChart].forEach(ch => {
            ch.options.scales.x.ticks.color = nt.tick;
            ch.options.scales.y.ticks.color = nt.tick;
            ch.options.scales.y.grid.color  = nt.grid;
            ch.update('none');
        });
        if (mChart.options.plugins.legend)
            mChart.options.plugins.legend.labels.color = nt.tick;
        mChart.update('none');
    });
    obs.observe(document.documentElement, { attributes: true, attributeFilter: ['data-pc-theme'] });
    obs.observe(document.body, { attributes: true, attributeFilter: ['data-pc-theme'] });
});
