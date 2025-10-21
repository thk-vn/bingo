@extends('master')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endsection

@section('main')
    <div class="app" role="application" aria-label="Bingo game">
        <div class="logo-fixed">BINGO ✨</div>
        <div class="user-info">
            <a href="{{ route('bingo.detail', auth('bingo')->user()) }}" id="info">{{ $userBingoName }} 🖋️</a>
        </div>
        <div id="win" class="win-banner" role="status">BINGO! 🎉</div>
        <div class="card-wrap" aria-live="polite">
            <div class="card-bingo">
                <div id="bingo" class="bingo" role="grid" aria-label="Bingo card">
                </div>
            </div>
            <div class="controls">
                <button id="reset" class="small reset">Reset</button>
            </div>
        </div>
        <div id="toast" class="toast">
            {{ __('view.bingo_user.start') }}
        </div>
    </div>
    <footer class="small">
        © 2025 - THK Holdings Vietnam
    </footer>

@section('script')
    <script>
        // Bingo logic
        (function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const bingoEl = document.getElementById('bingo');
            const winEl = document.getElementById('win');
            let card = []; // 5x5 numbers (or null for free)
            let marks = new Set(); // "r-c" keys
            const sessionToken = localStorage.getItem('session_token');
            const name = localStorage.getItem('name');
            const department = localStorage.getItem('department');
            const defaultMarkedCells = Array.from({
                length: 5
            }, () => Array(5).fill(false));
            const bingo_user = JSON.parse(localStorage.getItem('bingo_user'));

            // Save default marked_cells
            if (!localStorage.getItem('marked_cells')) {
                localStorage.setItem('marked_cells', JSON.stringify(defaultMarkedCells));
            }

            function showToast(message) {
                $toast.text(message).addClass('show');
                setTimeout(() => $toast.removeClass('show'), 2500);
            }

            async function fetchBoardGame() {
                try {
                    const url = "{{ route('bingo.fetch.board_game') }}";

                    const response = await fetch(url, {
                        method: "GET",
                        headers: {
                            "Accept": "application/json",
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                    });

                    // Check HTTP status
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const res = await response.json();

                    // Safely handle response
                    const bingoBoard = res?.data?.bingo_board ?? [];
                    const responseData = Array.isArray(bingoBoard) ? bingoBoard.flat() : [];

                    return responseData;
                } catch (err) {
                    console.error("Error fetching board game:", err);
                    return []; // always return an array so the caller won't break
                }
            }

            async function saveBoardGame() {
                try {
                    const bingo_board = JSON.parse(localStorage.getItem('bingo_board'));
                    const marked_cells = JSON.parse(localStorage.getItem('marked_cells'));
                    const url = '{{ route('bingo.save.board_game') }}';
                    const response = await fetch(url, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        body: JSON.stringify({
                            bingo_user,
                            bingo_board,
                            marked_cells
                        }),
                    });

                    // Check HTTP status
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                } catch (err) {
                    console.error(err);
                    showToast(registerErrorServer);
                }
            }

            async function resetBoardGame() {
                try {
                    const bingo_board = JSON.parse(localStorage.getItem('bingo_board'));
                    const marked_cells = JSON.parse(localStorage.getItem('marked_cells'));
                    const url = '{{ route('bingo.reset.board_game') }}';
                    const response = await fetch(url, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        body: JSON.stringify({
                            bingo_user,
                            bingo_board,
                            marked_cells
                        }),
                    });

                    // Parse JSON từ response
                    const res = await response.json();
                    if (response.ok) {
                        if (res?.data?.bingo_user) {
                            // Update localStorage bingo_user
                            localStorage.removeItem('bingo_user');
                            localStorage.setItem('bingo_user', JSON.stringify(res.data.bingo_user));
                            clearMarkedCells();
                        }
                    } else {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                } catch (err) {
                    console.error(err);
                    if (err.status === 422 && err.responseJSON?.errors) {
                        showToast(err.responseJSON.message);
                    } else {
                        showToast(registerErrorServer);
                    }
                }
            }

            function rndSample(total, min, max) {
                const pool = Array.from({
                    length: max - min + 1
                }, (_, i) => i + min);
                // Fisher-Yates shuffle
                for (let i = pool.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [pool[i], pool[j]] = [pool[j], pool[i]];
                }
                return pool.slice(0, total);
            }

            async function generateCard() {
                try {
                    const responseData = await fetchBoardGame(); // waiting Promise resolved

                    let numbers = [];
                    if (responseData && responseData.length > 0) {
                        // return response data
                        numbers = responseData;
                    } else {
                        // return random data
                        numbers = rndSample(25, 1, 50);
                    }

                    card = [];
                    for (let row = 0; row < 5; row++) {
                        card[row] = [];
                        for (let column = 0; column < 5; column++) {
                            const index = row * 5 + column;
                            card[row][column] = numbers[index];
                        }
                    }

                    // Save bingo_board into local storage
                    localStorage.setItem('bingo_board', JSON.stringify(card));
                    if (responseData && responseData.length === 0) {
                        await saveBoardGame();
                    }
                    marks.clear();
                    renderCard();
                    winEl.style.display = 'none';
                } catch (error) {
                    console.error('Error when generateCard:', error);
                }
            }

            // Restore marked_cells after reload page
            function restoreMarkedCells() {
                try {
                    const stored = JSON.parse(localStorage.getItem('marked_cells'));
                    if (!stored || !Array.isArray(stored)) return;

                    for (let r = 0; r < stored.length; r++) {
                        for (let c = 0; c < stored[r].length; c++) {
                            if (stored[r][c]) {
                                marks.add(`${r}-${c}`);
                                const cell = bingoEl.querySelector(`.cell[data-r="${r}"][data-c="${c}"]`);
                                if (cell) cell.classList.add('marked');
                            }
                        }
                    }

                    // Check if reload has BINGO then show
                    checkWin();
                } catch (err) {
                    console.error("Error restoring marked cells:", err);
                }
            }

            // Clear marked_cells function
            function clearMarkedCells() {
                try {
                    // Clear the in-memory marks Set
                    marks.clear();

                    // Remove the 'marked' class from all cells in the DOM
                    const cells = bingoEl.querySelectorAll('.cell.marked');
                    cells.forEach(cell => cell.classList.remove('marked'));

                    // Reset localStorage
                    const defaultMarkedCells = Array.from({
                        length: 5
                    }, () => Array(5).fill(false));
                    localStorage.setItem('marked_cells', JSON.stringify(defaultMarkedCells));

                    // Hide win banner if it was showing
                    winEl.style.display = 'none';
                } catch (err) {
                    console.error("Error clearing marked cells:", err);
                }
            }

            // Function render card UI
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
                        cell.textContent = n;
                        if (marks.has(`${r}-${c}`)) cell.classList.add('marked');
                        cell.addEventListener('click', onCellClick);
                        bingoEl.appendChild(cell);
                    }
                }
                // Add event reload page show previous marked_cells
                restoreMarkedCells();
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

                // Update localstorage marked_cells
                localStorage.removeItem('marked_cells');
                localStorage.setItem('marked_cells', JSON.stringify(M));

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
                const cells = document.querySelectorAll(".cell");
                const size = 5;
                let bingo = false;
                const lines = [];

                // Kiểm tra hàng ngang
                for (let i = 0; i < size; i++) {
                    const row = Array.from(cells).slice(i * size, i * size + size);
                    if (row.every(c => c.classList.contains("marked"))) {
                        lines.push(row);
                        bingo = true;
                    }
                }

                // Kiểm tra hàng dọc
                for (let i = 0; i < size; i++) {
                    const col = Array.from(cells).filter((_, idx) => idx % size === i);
                    if (col.every(c => c.classList.contains("marked"))) {
                        lines.push(col);
                        bingo = true;
                    }
                }

                // Kiểm tra chéo
                const diag1 = [0, 6, 12, 18, 24].map(i => cells[i]);
                const diag2 = [4, 8, 12, 16, 20].map(i => cells[i]);
                if (diag1.every(c => c.classList.contains("marked"))) {
                    lines.push(diag1);
                    bingo = true;
                }
                if (diag2.every(c => c.classList.contains("marked"))) {
                    lines.push(diag2);
                    bingo = true;
                }

                if (bingo) {
                    // Thêm hiệu ứng sóng sáng
                    document.querySelector(".card-wrap").classList.add("bingo-flash");
                    const winBanner = document.querySelector(".win-banner");
                    winBanner.classList.add("show-bingo");
                    winBanner.style.display = "block";

                    lines.forEach((line, index) => {
                        setTimeout(() => {
                            line.forEach(c => {
                                c.classList.add("bingo-wave");
                                setTimeout(() => c.classList.remove("bingo-wave"), 1600);
                            });
                        }, index * 300); // delay giữa các đường thắng
                    });

                    // Xóa hiệu ứng flash sau 1.5s
                    setTimeout(() => {
                        document.querySelector(".card-wrap").classList.remove("bingo-flash");
                    }, 1500);
                }
            }

            document.getElementById('reset').addEventListener('click', async () => {
                if (!confirm('Reset toàn bộ?')) return;
                await resetBoardGame();
                await generateCard();
            });

            // init
            generateCard();

            // expose some debug / helpful functions on window (optional)
            window._bingo = {
                generateCard,
                getState: () => ({
                    card,
                    marks: Array.from(marks),
                })
            };
        })();

        document.addEventListener("DOMContentLoaded", () => {
            const bingoCells = document.querySelectorAll(".cell");
            const winBanner = document.getElementById("win");
            const resetBtn = document.getElementById("reset");

            function triggerBingo(cells) {
                // Hiển thị banner
                winBanner.style.display = "block";
                winBanner.classList.add("show-bingo");

                // Hiệu ứng sáng lan từng ô
                cells.forEach((cell, i) => {
                    setTimeout(() => {
                        cell.classList.add("bingo-wave");
                    }, i * 150); // lan dần
                });

                // Tạo hiệu ứng nổ sáng xung quanh bảng
                document.querySelector(".card-wrap").classList.add("bingo-flash");
                setTimeout(() => {
                    document.querySelector(".card-wrap").classList.remove("bingo-flash");
                }, 1500);
            }

            // Nút reset
            resetBtn.addEventListener("click", () => {
                bingoCells.forEach(c => c.classList.remove("bingo-wave"));
                winBanner.style.display = "none";
            });
        });
    </script>
@endsection
