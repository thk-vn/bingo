@extends('master')

@section('main')
    <div class="app" role="application" aria-label="Bingo game">
        <div>
            <img class="logo" src="{{ Vite::asset('resources/images/logo.png') }}" alt="logo">
            <p class="lead">Nhấn "reset" để đổi bàn chơi khi Người dẫn trò thông báo có màn chơi mới.</p>
            <div class="card-wrap" aria-live="polite">
                <div class="bingo-header" aria-hidden="true">
                    <div class="col B">B</div>
                    <div class="col I">I</div>
                    <div class="col N">N</div>
                    <div class="col G">G</div>
                    <div class="col O">O</div>
                </div>

                <div class="card-bingo">
                    <div id="bingo" class="bingo" role="grid" aria-label="Bingo card">
                        <!-- cells injected by JS -->
                    </div>
                </div>

                <div class="controls">
                    <button id="reset" class="small reset">Reset</button>
                </div>

                <div id="win" class="win-banner" role="status">BINGO! 🎉</div>
            </div>

            <footer class="small">
                © 2025 - THK Holdings Vietnam
            </footer>
        </div>
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
            const winEl = document.getElementById('win');

            let card = []; // 5x5 numbers (or null for free)
            let marks = new Set(); // "r-c" keys
            let drawn = []; // numbers drawn
            let available = Array.from({
                length: 99
            }, (_, i) => i + 1);
            console.log(available)
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
                    // Set colums and numbers each columns
                    const need = 5;
                    const numbers = rndSample(rng, need);
                    // assign into rows
                    for (let row = 0; row < 5; row++) {
                        if (!card[row]) card[row] = [];
                        card[row][col] = numbers[row];
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
                        console.log();
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

            function updateUIAfterDraw(num, fromPrefill) {
                const numEl = document.createElement('div');
                numEl.className = 'num recent';
                numEl.textContent = num;
                // set timeout to remove recent highlight after a while
                setTimeout(() => numEl.classList.remove('recent'), 2200);
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

            document.getElementById('reset').addEventListener('click', () => {
                if (!confirm('Reset toàn bộ (thẻ, số đã bốc)?')) return;
                available = Array.from({
                    length: 75
                }, (_, i) => i + 1);
                generateCard();
            });

            // init
            generateCard();

            // expose some debug / helpful functions on window (optional)
            window._bingo = {
                generateCard,
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
