@extends('master')

@section('main')
    <div class="app" role="application" aria-label="Bingo game">
        <div>
            <h1>Bingo — Game nhanh</h1>
            <p class="lead">Nhấn "Tạo thẻ" để sinh thẻ mới. Bấm "Bốc số" để rút số ngẫu nhiên. Bạn có thể click ô để đánh
                dấu. Hệ thống tự nhận BINGO (hàng/cột/chéo).</p>

            <div class="card-wrap" aria-live="polite">
                <div class="bingo-header" aria-hidden="true">
                    <div class="col B">B</div>
                    <div class="col I">I</div>
                    <div class="col N">N</div>
                    <div class="col G">G</div>
                    <div class="col O">O</div>
                </div>

                <div id="bingo" class="bingo" role="grid" aria-label="Bingo card">
                    <!-- cells injected by JS -->
                </div>

                <div class="controls">
                    <button id="generate">Tạo thẻ</button>
                    <button id="draw" class="small">Bốc số</button>
                    <button id="auto" class="small secondary">Auto: OFF</button>
                    <button id="reset" class="small secondary">Reset</button>
                    <button id="clearMarks" class="small secondary">Xóa đánh dấu</button>
                </div>

                <div id="win" class="win-banner" role="status">BINGO! 🎉</div>
            </div>

            <footer class="small">Rules: thẻ 5×5 — cột B:1-15, I:16-30, N:31-45 (center free), G:46-60, O:61-75</footer>
        </div>

        <aside class="panel" aria-label="Bảng điều khiển">
            <h2 style="margin:0 0 8px 0; font-size:16px;">Số đã bốc</h2>
            <div id="drawn" class="drawn" aria-live="polite"></div>

            <div class="stats">
                <div>Đã bốc: <span id="drawCount">0</span>/75</div>
                <div>Last: <span id="lastNum">—</span></div>
            </div>

            <div style="margin-top:12px;">
                <label for="prefill" style="display:block; font-size:13px; color:var(--muted); margin-bottom:6px;">Tự thêm
                    số (ví dụ: 5,18,42)</label>
                <input id="prefill" type="text" placeholder="Nhập số, cách nhau bằng dấu phẩy"
                    style="width:100%; padding:8px; border-radius:8px; border:1px solid rgba(255,255,255,0.04); background:transparent; color:inherit;">
                <div style="display:flex; gap:8px; margin-top:8px;">
                    <button id="applyPrefill" class="small">Áp dụng</button>
                    <button id="removeLast" class="small secondary">Bỏ số cuối</button>
                </div>
            </div>

            <div style="margin-top:14px;">
                <button id="newCardSame" class="small">Tạo thẻ mới (giữ số đã bốc)</button>
            </div>

            <div style="margin-top:12px;">
                <button id="export" class="small">Export thẻ (PNG)</button>
            </div>

        </aside>
    </div>
@endsection


@section('script')
    <script>
        // Bingo logic
        (function() {
            const B_RANGE = [1, 15],
                I_RANGE = [16, 30],
                N_RANGE = [31, 45],
                G_RANGE = [46, 60],
                O_RANGE = [61, 75];
            const ranges = [B_RANGE, I_RANGE, N_RANGE, G_RANGE, O_RANGE];
            const bingoEl = document.getElementById('bingo');
            const drawnEl = document.getElementById('drawn');
            const drawCountEl = document.getElementById('drawCount');
            const lastNumEl = document.getElementById('lastNum');
            const winEl = document.getElementById('win');

            let card = []; // 5x5 numbers (or null for free)
            let marks = new Set(); // "r-c" keys
            let drawn = []; // numbers drawn
            let available = Array.from({
                length: 75
            }, (_, i) => i + 1);
            let autoTimer = null;

            function rndSample(range, count) {
                const [a, b] = range;
                const pool = [];
                for (let n = a; n <= b; n++) pool.push(n);
                // fisher-yates partial shuffle
                for (let i = pool.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [pool[i], pool[j]] = [pool[j], pool[i]];
                }
                return pool.slice(0, count).sort((x, y) => x - y);
            }

            function generateCard() {
                card = [];
                for (let col = 0; col < 5; col++) {
                    const rng = ranges[col];
                    // choose 5 for B,I,G,O; choose 4 for N (because center free)
                    const need = (col === 2 ? 4 : 5);
                    const nums = rndSample(rng, need);
                    // fill column
                    let colVals = [];
                    if (col === 2) {
                        // put first two, then free center, then remaining two
                        colVals = [nums[0], nums[1], null, nums[2], nums[3]];
                    } else {
                        colVals = nums;
                    }
                    // assign into rows
                    for (let row = 0; row < 5; row++) {
                        if (!card[row]) card[row] = [];
                        card[row][col] = colVals[row];
                    }
                }
                marks.clear();
                renderCard();
                winEl.style.display = 'none';
            }

            function renderCard() {
                bingoEl.innerHTML = '';
                for (let r = 0; r < 5; r++) {
                    for (let c = 0; c < 5; c++) {
                        const n = card[r][c];
                        const cell = document.createElement('div');
                        cell.className = 'cell';
                        cell.setAttribute('role', 'gridcell');
                        cell.dataset.r = r;
                        cell.dataset.c = c;
                        if (n === null) {
                            cell.classList.add('free');
                            cell.textContent = 'FREE';
                            marks.add(`${r}-${c}`); // free is always marked
                            cell.classList.add('marked');
                        } else {
                            cell.textContent = n;
                            if (marks.has(`${r}-${c}`)) cell.classList.add('marked');
                        }
                        cell.addEventListener('click', onCellClick);
                        bingoEl.appendChild(cell);
                    }
                }
            }

            function onCellClick(e) {
                const r = +this.dataset.r,
                    c = +this.dataset.c;
                const n = card[r][c];
                if (n === null) return; // free center can't toggle
                const key = `${r}-${c}`;
                if (marks.has(key)) {
                    marks.delete(key);
                    this.classList.remove('marked');
                } else {
                    // allow marking only if number was drawn OR allow manual marking? We'll allow manual marking too.
                    marks.add(key);
                    this.classList.add('marked');
                }
                checkWin();
            }

            function drawOne(fromPrefill = false) {
                if (available.length === 0) return null;
                // pick random from available
                const idx = Math.floor(Math.random() * available.length);
                const num = available.splice(idx, 1)[0];
                drawn.push(num);
                updateUIAfterDraw(num, fromPrefill);
                return num;
            }

            function updateUIAfterDraw(num, fromPrefill) {
                const numEl = document.createElement('div');
                numEl.className = 'num recent';
                numEl.textContent = num;
                // set timeout to remove recent highlight after a while
                setTimeout(() => numEl.classList.remove('recent'), 2200);
                drawnEl.prepend(numEl);
                drawCountEl.textContent = drawn.length;
                lastNumEl.textContent = num;
                // auto-mark numbers on card if exist
                autoMarkNumber(num);
                checkWin();
            }

            function autoMarkNumber(num) {
                for (let r = 0; r < 5; r++) {
                    for (let c = 0; c < 5; c++) {
                        if (card[r][c] === num) {
                            marks.add(`${r}-${c}`);
                            // update DOM cell
                            const cell = bingoEl.querySelector(`.cell[data-r="${r}"][data-c="${c}"]`);
                            if (cell) cell.classList.add('marked');
                        }
                    }
                }
            }

            function checkWin() {
                // return if any full row/col/diag marked
                // prepare matrix of booleans
                const M = Array.from({
                    length: 5
                }, () => Array(5).fill(false));
                for (const key of marks) {
                    const [r, c] = key.split('-').map(Number);
                    M[r][c] = true;
                }
                // check rows
                for (let r = 0; r < 5; r++) {
                    let ok = true;
                    for (let c = 0; c < 5; c++)
                        if (!M[r][c]) {
                            ok = false;
                            break;
                        }
                    if (ok) {
                        showWin();
                        highlightLine('row', r);
                        return true;
                    }
                }
                // check cols
                for (let c = 0; c < 5; c++) {
                    let ok = true;
                    for (let r = 0; r < 5; r++)
                        if (!M[r][c]) {
                            ok = false;
                            break;
                        }
                    if (ok) {
                        showWin();
                        highlightLine('col', c);
                        return true;
                    }
                }
                // diag TL-BR
                let ok = true;
                for (let i = 0; i < 5; i++)
                    if (!M[i][i]) {
                        ok = false;
                        break;
                    }
                if (ok) {
                    showWin();
                    highlightLine('diag', 0);
                    return true;
                }
                // diag TR-BL
                ok = true;
                for (let i = 0; i < 5; i++)
                    if (!M[i][4 - i]) {
                        ok = false;
                        break;
                    }
                if (ok) {
                    showWin();
                    highlightLine('diag', 1);
                    return true;
                }

                // no win
                winEl.style.display = 'none';
                removeHighlights();
                return false;
            }

            function highlightLine(type, idx) {
                // add subtle glow to the winning cells
                removeHighlights();
                if (type === 'row') {
                    for (let c = 0; c < 5; c++) {
                        const cell = bingoEl.querySelector(`.cell[data-r="${idx}"][data-c="${c}"]`);
                        if (cell) cell.style.boxShadow = '0 8px 22px rgba(16,185,129,0.18)';
                    }
                } else if (type === 'col') {
                    for (let r = 0; r < 5; r++) {
                        const cell = bingoEl.querySelector(`.cell[data-r="${r}"][data-c="${idx}"]`);
                        if (cell) cell.style.boxShadow = '0 8px 22px rgba(16,185,129,0.18)';
                    }
                } else if (type === 'diag') {
                    if (idx === 0) {
                        for (let i = 0; i < 5; i++) {
                            const cell = bingoEl.querySelector(`.cell[data-r="${i}"][data-c="${i}"]`);
                            if (cell) cell.style.boxShadow = '0 8px 22px rgba(16,185,129,0.18)';
                        }
                    } else {
                        for (let i = 0; i < 5; i++) {
                            const cell = bingoEl.querySelector(`.cell[data-r="${i}"][data-c="${4-i}"]`);
                            if (cell) cell.style.boxShadow = '0 8px 22px rgba(16,185,129,0.18)';
                        }
                    }
                }
            }

            function removeHighlights() {
                const cells = bingoEl.querySelectorAll('.cell');
                cells.forEach(c => c.style.boxShadow = '');
            }

            function showWin() {
                winEl.style.display = 'block';
                // small confetti-ish effect: add numbers highlight quickly
                // (we won't add heavy animation to keep it simple)
            }

            // UI buttons
            document.getElementById('generate').addEventListener('click', () => {
                generateCard();
            });

            document.getElementById('draw').addEventListener('click', () => {
                drawOne();
            });

            document.getElementById('reset').addEventListener('click', () => {
                if (!confirm('Reset toàn bộ (thẻ, số đã bốc)?')) return;
                available = Array.from({
                    length: 75
                }, (_, i) => i + 1);
                drawn = [];
                drawnEl.innerHTML = '';
                drawCountEl.textContent = '0';
                lastNumEl.textContent = '—';
                generateCard();
            });

            document.getElementById('clearMarks').addEventListener('click', () => {
                marks.clear();
                // re-add center free
                marks.add('2-2');
                renderCard();
            });

            // apply manual prefill numbers
            document.getElementById('applyPrefill').addEventListener('click', () => {
                const txt = document.getElementById('prefill').value.trim();
                if (!txt) return alert('Nhập ít nhất 1 số.');
                const parts = txt.split(',').map(s => parseInt(s.trim(), 10)).filter(n => !isNaN(n) && n >= 1 &&
                    n <= 75);
                parts.forEach(n => {
                    // if n not already drawn, remove it from available and add to drawn
                    if (!drawn.includes(n)) {
                        const idx = available.indexOf(n);
                        if (idx !== -1) available.splice(idx, 1);
                        drawn.push(n);
                        updateUIAfterDraw(n, true);
                    }
                });
            });

            document.getElementById('removeLast').addEventListener('click', () => {
                const last = drawn.pop();
                if (!last) return;
                available.push(last);
                // remove from UI
                const firstChild = drawnEl.querySelector('.num');
                if (firstChild) drawnEl.removeChild(firstChild);
                drawCountEl.textContent = drawn.length;
                lastNumEl.textContent = drawn[drawn.length - 1] || '—';
            });

            // new card but keep drawn numbers (useful when you want fresh card against same draws)
            document.getElementById('newCardSame').addEventListener('click', () => {
                generateCard();
                // re-mark numbers that were already drawn
                drawn.forEach(n => autoMarkNumber(n));
                renderCard();
                checkWin();
            });

            // export card as image
            document.getElementById('export').addEventListener('click', () => {
                exportCardAsPNG();
            });

            // auto draw toggle
            document.getElementById('auto').addEventListener('click', function() {
                if (autoTimer) {
                    clearInterval(autoTimer);
                    autoTimer = null;
                    this.textContent = 'Auto: OFF';
                } else {
                    autoTimer = setInterval(() => {
                        if (available.length === 0) {
                            clearInterval(autoTimer);
                            autoTimer = null;
                            document.getElementById('auto').textContent = 'Auto: OFF';
                            return;
                        }
                        drawOne();
                    }, 1200);
                    this.textContent = 'Auto: ON';
                }
            });

            // simple export using canvas rendering of DOM (basic)
            function exportCardAsPNG() {
                // build small canvas representing the card
                const canvas = document.createElement('canvas');
                const cellSize = 140;
                const pad = 24;
                canvas.width = cellSize * 5 + pad * 2;
                canvas.height = cellSize * 5 + pad * 2 + 40;
                const ctx = canvas.getContext('2d');
                // background
                ctx.fillStyle = '#071126';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                // title
                ctx.fillStyle = '#ffd166';
                ctx.font = '26px sans-serif';
                ctx.fillText('BINGO', pad, 34);
                ctx.font = 'bold 22px sans-serif';
                // draw cells
                for (let r = 0; r < 5; r++) {
                    for (let c = 0; c < 5; c++) {
                        const x = pad + c * cellSize;
                        const y = 50 + r * cellSize;
                        // box
                        ctx.fillStyle = '#0b1a2d';
                        ctx.fillRect(x + 8, y + 8, cellSize - 16, cellSize - 16);
                        ctx.strokeStyle = '#16435f';
                        ctx.lineWidth = 6;
                        ctx.strokeRect(x + 8, y + 8, cellSize - 16, cellSize - 16);
                        // number
                        const n = card[r][c];
                        ctx.fillStyle = n === null ? '#34d399' : '#e6eef8';
                        ctx.font = 'bold 48px sans-serif';
                        const txt = n === null ? 'FREE' : String(n);
                        const tw = ctx.measureText(txt).width;
                        ctx.fillText(txt, x + 8 + (cellSize - 16 - tw) / 2, y + 70);
                    }
                }
                // download image
                const a = document.createElement('a');
                a.href = canvas.toDataURL('image/png');
                a.download = 'bingo-card.png';
                a.click();
            }

            // init
            generateCard();

            // expose some debug / helpful functions on window (optional)
            window._bingo = {
                generateCard,
                drawOne,
                getState: () => ({
                    card,
                    marks: Array.from(marks),
                    drawn,
                    availableLength: available.length
                })
            };

        })();
    </script>
@endsection
