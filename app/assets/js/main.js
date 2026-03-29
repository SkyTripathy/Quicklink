document.addEventListener("DOMContentLoaded", () => {
    Chart.defaults.color = "#94a3b8";
    Chart.defaults.font.family = "'Inter', sans-serif";
    
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 12, padding: 20 }
            }
        }
    };

    // 1. Clicks Line Chart
    const ctxClicks = document.getElementById('clicksChart').getContext('2d');
    const gradientBlue = ctxClicks.createLinearGradient(0, 0, 0, 400);
    gradientBlue.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
    gradientBlue.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    const clicksChart = new Chart(ctxClicks, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Clicks',
                data: [0, 0, 0, 0, 0, 0, 0],
                borderColor: '#3b82f6',
                backgroundColor: gradientBlue,
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#0b0f19',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                    ticks: { precision: 0 }
                },
                x: {
                    grid: { display: false, drawBorder: false }
                }
            },
            plugins: { legend: { display: false } }
        }
    });

    // 2. Traffic Sources (Doughnut)
    const ctxSources = document.getElementById('sourcesChart').getContext('2d');
    const sourcesChart = new Chart(ctxSources, {
        type: 'doughnut',
        data: {
            labels: ['No Data'],
            datasets: [{
                data: [1],
                backgroundColor: ['#1e293b'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: { ...commonOptions, cutout: '75%' }
    });

    // 3. Campaigns (Bar)
    const ctxCampaigns = document.getElementById('campaignsChart').getContext('2d');
    const campaignsChart = new Chart(ctxCampaigns, {
        type: 'bar',
        data: {
            labels: ['No campaigns yet'],
            datasets: [{ label: 'Clicks', data: [0], backgroundColor: '#8b5cf6', borderRadius: 4 }]
        },
        options: {
            ...commonOptions,
            plugins: { legend: { display: false } },
            scales: { y: { display: false }, x: { grid: { display: false } } }
        }
    });

    // 4. Countries - now rendered as a custom list (no Chart.js)

    // 5. Mediums (Pie)
    const ctxMediums = document.getElementById('mediumsChart').getContext('2d');
    const mediumsChart = new Chart(ctxMediums, {
        type: 'pie',
        data: {
            labels: ['No Data'],
            datasets: [{
                data: [1],
                backgroundColor: ['#1e293b'],
                borderWidth: 0
            }]
        },
        options: { ...commonOptions }
    });

    // --- Fetch Real Data ---
    const bgColors = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899'];

    fetch('api/analytics.php')
        .then(res => res.json())
        .then(data => {
            if (data.error) return;

            // Update Stats
            document.getElementById('stat-clicks').innerText = data.total_clicks;
            document.getElementById('stat-today').innerText = data.clicks_today;
            document.getElementById('stat-links').innerText = data.active_links;
            document.getElementById('stat-source').innerText = data.top_source;
            document.getElementById('stat-source-cnt').innerText = data.top_source_count;

            // Countries card with flags
            if (data.countries_detail && data.countries_detail.length > 0) {
                const top = data.countries_detail[0];
                document.getElementById('stat-country-name').innerText = top.name;
                if (top.code) {
                    document.getElementById('stat-country-flag').src = 'https://flagcdn.com/32x24/' + top.code + '.png';
                }
                // Mini flags for other countries
                const subEl = document.getElementById('stat-country-sub');
                const others = data.countries_detail.slice(1, 4);
                if (others.length > 0) {
                    subEl.innerHTML = others.map(c => {
                        const flagImg = c.code ? '<img src="https://flagcdn.com/16x12/' + c.code + '.png" style="width:14px; border-radius:1px; vertical-align:middle;">' : '';
                        return '<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(255,255,255,0.05); padding:2px 8px; border-radius:4px; font-size:11px;">' + flagImg + c.name + '</span>';
                    }).join('');
                } else {
                    subEl.innerHTML = top.cnt + ' clicks';
                }
            }

            // Clicks Over Time
            if (data.chart_clicks.labels.length > 0) {
                clicksChart.data.labels = data.chart_clicks.labels;
                clicksChart.data.datasets[0].data = data.chart_clicks.data;
                clicksChart.update();
            }

            // Sources
            if (data.chart_sources.labels.length > 0) {
                sourcesChart.data.labels = data.chart_sources.labels;
                sourcesChart.data.datasets[0].data = data.chart_sources.data;
                sourcesChart.data.datasets[0].backgroundColor = bgColors;
                sourcesChart.update();
            }

            // Campaigns
            if (data.chart_campaigns.labels.length > 0) {
                campaignsChart.data.labels = data.chart_campaigns.labels;
                campaignsChart.data.datasets[0].data = data.chart_campaigns.data;
                campaignsChart.data.datasets[0].backgroundColor = '#8b5cf6';
                campaignsChart.update();
            }

            // Mediums
            if (data.chart_mediums.labels.length > 0) {
                mediumsChart.data.labels = data.chart_mediums.labels;
                mediumsChart.data.datasets[0].data = data.chart_mediums.data;
                mediumsChart.data.datasets[0].backgroundColor = bgColors;
                mediumsChart.update();
            }

            // Countries list with flags
            if (data.countries_detail && data.countries_detail.length > 0) {
                const list = document.getElementById('countriesList');
                const maxCnt = data.countries_detail[0].cnt;
                list.innerHTML = data.countries_detail.slice(0, 5).map(c => {
                    const pct = Math.round((c.cnt / maxCnt) * 100);
                    const flagSrc = c.code ? 'https://flagcdn.com/24x18/' + c.code + '.png' : '';
                    const flagImg = flagSrc ? '<img src="' + flagSrc + '" style="width:20px; border-radius:2px; box-shadow:0 1px 3px rgba(0,0,0,0.3);">' : '<i class="fa-solid fa-globe" style="font-size:14px; opacity:0.4;"></i>';
                    return '<div style="display:flex; align-items:center; gap:10px;">'
                        + flagImg
                        + '<div style="flex:1; min-width:0;">'
                        + '<div style="display:flex; justify-content:space-between; margin-bottom:3px;">'
                        + '<span style="font-size:12px; font-weight:500;">' + c.name + '</span>'
                        + '<span style="font-size:11px; color:var(--text-secondary);">' + c.cnt + '</span>'
                        + '</div>'
                        + '<div style="height:4px; background:rgba(255,255,255,0.06); border-radius:2px; overflow:hidden;">'
                        + '<div style="height:100%; width:' + pct + '%; background:linear-gradient(90deg,#06b6d4,#3b82f6); border-radius:2px;"></div>'
                        + '</div></div></div>';
                }).join('');
            }
        })
        .catch(err => console.error("Could not fetch analytics:", err));
});
