/* =====================================================
         Charts
         ===================================================== */


var cpuChart = new ApexCharts(document.querySelector('#cpu-chart'), {
    chart: { type: 'area', height: '100%', toolbar:{show:false}, background:'transparent',
        animations:{enabled:true,easing:'easeinout',speed:800} },
    series: [{ name: 'CPU %', data: cpuChartData }],
    xaxis: { categories: cpuChartLabels, labels:{style:{colors:'#64748b',fontSize:'10px'}}, axisBorder:{show:false}, axisTicks:{show:false} },
    yaxis: { min:0, max:100, labels:{style:{colors:'#64748b',fontSize:'10px'}, formatter: v => v+'%'} },
    stroke: { curve:'smooth', width:2.5 },
    fill: { type:'gradient', gradient:{opacityFrom:.35, opacityTo:.02} },
    colors: ['#6c63ff'],
    grid: { borderColor:'rgba(255,255,255,.05)', strokeDashArray:4 },
    tooltip: { theme:'dark', y:{formatter: v => v+'%'} },
    markers: { size:0 },
    theme: { mode:'dark' }
});
cpuChart.render();

var reqChart = new ApexCharts(document.querySelector('#req-chart'), {
    chart: { type:'bar', height:150, toolbar:{show:false}, background:'transparent' },
    series: [{ name:'درخواست', data: reqChartData }],
    xaxis: { categories: reqChartLabels, labels:{style:{colors:'#64748b',fontSize:'9px'}, rotate:-45}, axisBorder:{show:false}, axisTicks:{show:false} },
    yaxis: { labels:{style:{colors:'#64748b',fontSize:'10px'}} },
    colors: ['#4fc3f7'],
    plotOptions: { bar:{borderRadius:3, columnWidth:'60%'} },
    fill: { type:'gradient', gradient:{shade:'dark',type:'vertical',opacityFrom:1,opacityTo:.6} },
    grid: { borderColor:'rgba(255,255,255,.05)', strokeDashArray:4 },
    tooltip: { theme:'dark' },
    theme: { mode:'dark' }
});
reqChart.render();

/* =====================================================
   Gauge ring helper
   ===================================================== */
function updateRing(id, pct, color) {
    var el = document.getElementById(id);
    if (!el) return;
    el.style.background = 'conic-gradient(' + color + ' ' + (pct * 3.6) + 'deg, rgba(255,255,255,.06) 0%)';
    el.style.boxShadow  = '0 0 18px ' + color + '33';
}

/* =====================================================
   SSE - Server-Sent Events
   ===================================================== */

var eventSource = null;
var reconnectTimer = null;

function connectSSE() {
    if (eventSource) { eventSource.close(); }

    setStatus('loading', 'در حال اتصال...');

    eventSource = new EventSource(sseUrl);

    eventSource.addEventListener('pulse', function(e) {
        try {
            var d = JSON.parse(e.data);
            updateDashboard(d);
            setStatus('ok', 'متصل — به‌روزرسانی هر ۱۰ ثانیه');
            document.getElementById('last-update').textContent =
                'آخرین به‌روزرسانی: ' + new Date().toLocaleTimeString('fa-IR');
        } catch(err) {
            console.error('SSE parse error:', err);
        }
    });

    eventSource.onerror = function() {
        setStatus('error', 'قطع شد — تلاش مجدد در ۱۵ ثانیه');
        eventSource.close();
        clearTimeout(reconnectTimer);
        reconnectTimer = setTimeout(connectSSE, 15000);
    };
}

function setStatus(type, text) {
    var dot = document.getElementById('liveDot');
    var st  = document.getElementById('sse-status');
    dot.className = 'live-dot ' + (type === 'ok' ? '' : type);
    if (st) st.textContent = text;
}

/* =====================================================
   Update all DOM elements from SSE data
   ===================================================== */
function updateDashboard(d) {
    var p = d.pulse;

    // KPIs
    setText('kpi-cpu',   (p.cpu || 0) + '%');
    setText('kpi-mem',   (p.memory_used || 0) + ' MB');
    setText('kpi-req',   fmtNum(p.total_requests || 0));
    setText('kpi-slow',  p.slow_requests_count || 0);
    setText('kpi-exc',   p.exceptions_count || 0);
    setText('kpi-cache', (p.cache_hit_rate || 0) + '%');

    // KPI subs
    var cpu = p.cpu || 0;
    setClass('kpi-cpu-sub', cpu > 80 ? 'bad' : cpu > 60 ? 'warn' : 'ok');
    setText('kpi-cpu-sub', cpu > 80 ? '⚠ بالا' : '✓ نرمال');

    setText('kpi-mem-sub', (p.memory_percent || 0) + '% مصرف');
    setText('kpi-req-sub', (p.requests_per_min || 0) + '/min');

    var slow = p.slow_requests_count || 0;
    setClass('kpi-slow', slow > 0 ? 'p-kpi-val bad' : 'p-kpi-val');
    setText('kpi-slow-sub', slow > 0 ? 'نیاز به بررسی' : '✓ بدون مشکل');
    setClass('kpi-slow-sub', slow > 0 ? 'p-kpi-sub bad' : 'p-kpi-sub ok');

    var exc = p.exceptions_count || 0;
    setClass('kpi-exc', exc > 0 ? 'p-kpi-val bad' : 'p-kpi-val');
    setText('kpi-exc-sub', exc > 10 ? '⚠ زیاد' : exc > 0 ? '⚠ دارد' : '✓ پاک');

    var cr = p.cache_hit_rate || 0;
    setClass('kpi-cache-sub', cr >= 80 ? 'p-kpi-sub ok' : 'p-kpi-sub warn');
    setText('kpi-cache-sub', cr >= 80 ? '✓ خوب' : '⚠ بهینه‌سازی');

    // Gauges
    updateRing('ring-cpu',  cpu,                  '#6c63ff');
    updateRing('ring-mem',  p.memory_percent || 0,'#00d4aa');
    updateRing('ring-disk', p.disk_percent    || 0,'#f6c90e');
    setText('ring-cpu-val',  cpu + '%');
    setText('ring-mem-val',  (p.memory_percent || 0) + '%');
    setText('ring-disk-val', (p.disk_percent   || 0) + '%');

    // Meters
    setWidth('meter-ram',  (p.memory_percent || 0) + '%');
    setWidth('meter-disk', (p.disk_percent   || 0) + '%');
    setWidth('meter-db',   Math.min((p.db_connections || 0) * 4, 100) + '%');
    setText('mem-detail',  (p.memory_used || 0) + ' / ' + (p.memory_total || 0) + ' MB');
    setText('db-conn-val', p.db_connections || 0);

    // CPU chart — append new point
    if (cpuChart && p.cpu !== undefined) {
        cpuChartData.push(p.cpu);
        cpuChartData.shift();
        cpuChartLabels.push(new Date().toLocaleTimeString('fa-IR',{hour:'2-digit',minute:'2-digit'}));
        cpuChartLabels.shift();
        cpuChart.updateSeries([{data: cpuChartData}]);
    }

    // Cache donut
    updateCache(p);

    // Queue summary
    var qt = Math.max((p.queue_done||0)+(p.queue_running||0)+(p.queue_failed||0)+(p.queue_pending||0), 1);
    [['queue_done','#43e97b','mf-green'],['queue_running','#4fc3f7','mf-blue'],['queue_pending','#f6c90e','mf-yellow'],['queue_failed','#ff6b6b','mf-red']].forEach(function(item){
        setText('q-' + item[0], fmtNum(p[item[0]] || 0));
        setWidth('q-meter-' + item[0], Math.round((p[item[0]]||0)/qt*100) + '%');
    });

    // Slow requests table
    if (d.slow_requests !== undefined) {
        setText('slow-req-count', d.slow_requests.length + ' مورد');
        renderSlowRequests(d.slow_requests);
    }
    // Slow queries table
    if (d.slow_queries !== undefined) {
        setText('slow-qry-count', d.slow_queries.length + ' مورد');
        renderSlowQueries(d.slow_queries);
    }
    // Exceptions
    if (d.exceptions !== undefined) {
        setText('exc-count', d.exceptions.length + ' نوع');
        renderExceptions(d.exceptions);
    }
    // Queue jobs
    if (d.queue_jobs !== undefined) {
        renderQueueJobs(d.queue_jobs);
    }
}

/* =====================================================
   Render helpers (بازسازی tbody)
   ===================================================== */
function renderSlowRequests(rows) {
    var el = document.getElementById('slow-req-body');
    if (!el) return;
    if (!rows.length) { showEmpty('slow-req-table', 'slow-req-empty'); return; }
    hideEmpty('slow-req-table', 'slow-req-empty');
    el.innerHTML = rows.map(function(r) {
        var cls = r.duration > 2000 ? 'd-bad' : r.duration > 1000 ? 'd-warn' : 'd-ok';
        return '<tr>' +
            '<td><span class="m-badge m-' + r.method.toLowerCase() + '">' + r.method + '</span></td>' +
            '<td style="max-width:220px;"><span style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--pt);" title="' + r.uri + '">' + r.uri + '</span></td>' +
            '<td style="text-align:right;"><span class="d-badge ' + cls + '">' + fmtNum(r.duration) + 'ms</span></td>' +
            '<td style="font-size:10px;color:var(--pm);white-space:nowrap;">' + r.time_ago + '<br><span style="font-size:9px;opacity:.6;">' + r.time + '</span></td>' +
            '</tr>';
    }).join('');
}

function renderSlowQueries(rows) {
    var el = document.getElementById('slow-qry-body');
    if (!el) return;
    if (!rows.length) { showEmpty('slow-qry-table', 'slow-qry-empty'); return; }
    hideEmpty('slow-qry-table', 'slow-qry-empty');
    el.innerHTML = rows.map(function(r) {
        var cls = r.duration > 1000 ? 'd-bad' : r.duration > 500 ? 'd-warn' : 'd-ok';
        var sql = r.sql.length > 80 ? r.sql.substring(0,80)+'...' : r.sql;
        return '<tr>' +
            '<td><div class="sql-code" title="' + r.sql + '">' + sql + '</div></td>' +
            '<td style="text-align:right;"><span class="d-badge ' + cls + '">' + fmtNum(r.duration) + 'ms</span></td>' +
            '<td style="font-size:10px;color:var(--pm);white-space:nowrap;">' + r.time_ago + '<br><span style="font-size:9px;opacity:.6;">' + r.time + '</span></td>' +
            '</tr>';
    }).join('');
}

function renderExceptions(rows) {
    var el = document.getElementById('exc-body');
    if (!el) return;
    if (!rows.length) {
        el.innerHTML = '<div class="p-empty"><i class="feather icon-check-circle" style="color:#43e97b;opacity:.8;font-size:28px;display:block;margin-bottom:8px;"></i><span style="font-size:12px;">هیچ خطایی ثبت نشده ✓</span></div>';
        return;
    }
    el.innerHTML = rows.map(function(e) {
        return '<div class="exc-block">' +
            '<div class="exc-class" title="' + e.class + '">' + e.class_short + ' <span style="font-size:10px;color:var(--pm);font-weight:400;">' + e.class + '</span></div>' +
            '<div class="exc-msg">' + e.message + '</div>' +
            '<div class="exc-meta"><span class="d-badge d-bad">' + e.count + ' بار</span> <span class="exc-time">آخرین بار: ' + e.time_ago + '</span> <span style="font-size:10px;color:var(--pm);opacity:.6;">' + e.last_seen + '</span></div>' +
            '</div>';
    }).join('');
}

function renderQueueJobs(rows) {
    var el = document.getElementById('queue-body');
    if (!el) return;
    var statusLabel = { done:'✓ موفق', running:'⟳ اجرا', failed:'✗ شکست', pending:'⏳ انتظار', slow:'🐢 کند' };
    el.innerHTML = rows.map(function(j) {
        var dur = j.duration !== null
            ? '<span class="d-badge ' + (j.duration > 30 ? 'd-bad' : j.duration > 10 ? 'd-warn' : 'd-ok') + '">' + j.duration + 's</span>'
            : '<span style="color:var(--pm);">—</span>';
        return '<tr>' +
            '<td style="font-family:monospace;font-size:11px;color:#a78bfa;white-space:nowrap;">' + j.job_short + '</td>' +
            '<td style="max-width:180px;"><span style="font-size:10px;color:var(--pm);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + j.job + '">' + j.job + '</span></td>' +
            '<td><span class="d-badge d-info">' + j.queue + '</span></td>' +
            '<td><span class="q-badge q-' + j.status + '">' + (statusLabel[j.status] || j.status) + '</span></td>' +
            '<td style="text-align:right;">' + dur + '</td>' +
            '<td style="font-size:10px;color:var(--pm);white-space:nowrap;">' + j.time_ago + '<br><span style="font-size:9px;opacity:.6;">' + j.time + '</span></td>' +
            '</tr>';
    }).join('');
}

function updateCache(p) {
    var hits    = p.cache_hits   || 0;
    var misses  = p.cache_misses || 0;
    var writes  = p.cache_writes || 0;
    var total   = Math.max(hits + misses, 1);
    var rate    = Math.round(hits / total * 100 * 10) / 10;
    var r       = 38; var circ = 2 * Math.PI * r;
    var hl      = circ * (rate / 100);
    var ml      = circ * (1 - rate / 100);

    setText('cache-hits-val',  fmtNum(hits));
    setText('cache-miss-val',  fmtNum(misses));
    setText('cache-write-val', fmtNum(writes));
    setText('cache-rate-text', rate + '%');
    setText('cache-rate-label',rate + '%');
    setWidth('meter-cache', rate + '%');

    var hitArc  = document.getElementById('cache-hit-arc');
    var missArc = document.getElementById('cache-miss-arc');
    if (hitArc) {
        hitArc.setAttribute('stroke-dasharray',  hl + ' ' + (circ - hl));
    }
    if (missArc) {
        missArc.setAttribute('stroke-dasharray',  ml + ' ' + (circ - ml));
        missArc.setAttribute('stroke-dashoffset', circ * 0.25 - hl);
    }
}

/* =====================================================
   Force refresh
   ===================================================== */
function forceRefresh() {
    fetch(forceRefreshAPI, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    }).then(r => r.json()).then(function(res) {
        if (res.success) updateDashboard(res.data);
    });
}

/* =====================================================
   Tiny DOM helpers
   ===================================================== */
function setText(id, v) { var el=document.getElementById(id); if(el) el.textContent=v; }
function setWidth(id, v) { var el=document.getElementById(id); if(el) el.style.width=v; }
function setClass(id, c) { var el=document.getElementById(id); if(el) el.className=c; }
function fmtNum(n) { return Number(n).toLocaleString('fa-IR'); }
function showEmpty(tableId, emptyId) {
    var t=document.getElementById(tableId), e=document.getElementById(emptyId);
    if(t) t.style.display='none'; if(e) e.style.display='block';
}
function hideEmpty(tableId, emptyId) {
    var t=document.getElementById(tableId), e=document.getElementById(emptyId);
    if(t) t.style.display=''; if(e) e.style.display='none';
}

/* =====================================================
   Boot
   ===================================================== */
document.addEventListener('DOMContentLoaded', function() {
    connectSSE();
});
