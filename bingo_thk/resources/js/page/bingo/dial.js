import backgroundUrl from '../../../images/background_bingo_2.png';

document.addEventListener('DOMContentLoaded', () => {
    init();
    document.getElementById('container').addEventListener('click', () => {
        if (!isSpinning && !winnerBallMoving) {
            startSpin();
        }
    });

    document.querySelector('.btn-reset').addEventListener('click', () => {
        resetGame();
    });

    // Click vào khung phép tính để ẩn khung và cho ball trúng thưởng hiện ra.
    const mathChallengeButton = document.getElementById('mathChallengeButton');
    if (mathChallengeButton) {
        mathChallengeButton.addEventListener('click', revealMathChallengeWinner);
    }
});

let scene, camera, renderer, sphereGroup, ballsGroup; // Các đối tượng Three.js chính
let balls = []; // Mảng chứa tất cả quả cầu số
let rotationSpeed = { x: 0, y: 0};
let isSpinning = false; // Trạng thái đang quay hay không
let winnerBall = null; // Quả cầu trúng thưởng hiện tại
let drawnNumbers = []; // Mảng lưu các số đã quay
let numbersGrid = []; // Mảng lưu các ô số trong grid
let winnerFloatingElement = null; // Element hiển thị số trúng thưởng
let winnerBallMoving = false; // Trạng thái quả cầu đang di chuyển đến grid
let winnerBallTarget = null; // Vị trí đích của quả cầu trúng thưởng
let numberOfBalls = 70 // Tổng số quả cầu hiển thị
let pendingSpin = false; // Đánh dấu người dùng đã click để quay tiếp sau khi di chuyển winner
let specialWinnerNumbers = []; // Mảng lưu các số cần đánh dấu
let isMathChallengeShowing = false; // Trạng thái đang hiển thị khung phép tính
let isMathChallengeClosing = false; // Trạng thái khung phép tính đang thu nhỏ
const timePickWinner = 1500;
const mathChallengeInterval = 5; // Cứ mỗi 5 lượt quay sẽ hiện phép tính trước khi show ball
const maxMathOperand = 69; // Tất cả số xuất hiện trong phép tính phải nhỏ hơn 70
const mathChallengeCloseDuration = 360; // Khớp với thời gian transition thu nhỏ trong CSS
const winnerMoveStartPos = new THREE.Vector3(0, 0, 5);
const winnerTargetPos = new THREE.Vector3();
const sphereRadius = 6.5;

const textureLoader = new THREE.TextureLoader();
const backgroundTexture = textureLoader.load(backgroundUrl);

let canvasTextureCache = null;
let isTexturesCached = false;
let sharedSphereMaterial = null;

const geometryPool = {
    sphere: null,
    plane: {},

    getSphereGeometry() {
        if (!this.sphere) {
            this.sphere = new THREE.SphereGeometry(0.6, 16, 16);
        }
        return this.sphere;
    },

    getPlaneGeometry(width, height) {
        const key = `${width}_${height}`;
        if (!this.plane[key]) {
            this.plane[key] = new THREE.PlaneGeometry(width, height);
        }
        return this.plane[key];
    },

    dispose() {
        if (this.sphere) {
            this.sphere.dispose();
            this.sphere = null;
        }
        Object.values(this.plane).forEach(geom => geom.dispose());
        this.plane = {};
    }
};

function init() {
    // Tạo scene 3D với background
    scene = new THREE.Scene();
    scene.background = backgroundTexture;

    // Tạo camera phối cảnh với góc nhìn 60 độ
    camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
    // Đặt camera ở vị trí z, tăng -> ball nhỏ, giảm -> ball to
    camera.position.z = 14;

    // Tạo renderer WebGL với khử răng cưa
    renderer = new THREE.WebGLRenderer({
        antialias: true
    });
    //Giới hạn pixel ratio WebGL
    //Render nét hơn trên màn hình DPI cao, nhưng giới hạn tối đa 1.5 để tránh tụt FPS
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));


    // Cập nhật kích thước renderer
    updateRendererSize();
    document.getElementById('container').appendChild(renderer.domElement);

    // ===== THIẾT LẬP ÁNH SÁNG =====
    // Ánh sáng môi trường (ambient light) - chiếu sáng đều mọi hướng
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
    scene.add(ambientLight);

    // Ánh sáng điểm 1 - chiếu sáng chính từ góc phải trên
    const pointLight1 = new THREE.PointLight(0xffffff, 1, 100);
    pointLight1.position.set(10, 10, 10);
    scene.add(pointLight1);

    // Ánh sáng điểm 2 chiếu sáng phụ từ góc trái dưới
    const pointLight2 = new THREE.PointLight(0xffffff, 0.5, 100);
    pointLight2.position.set(-10, -10, 5);
    scene.add(pointLight2);

    // ===== TẠO CÁC NHÓM ĐỐI TƯỢNG =====
    // Nhóm chứa khung lưới sphere
    sphereGroup = new THREE.Group();
    scene.add(sphereGroup);

    // Nhóm chứa các quả cầu số bên trong sphere
    ballsGroup = new THREE.Group();
    scene.add(ballsGroup);

    // Tạo khung lưới sphere
    createDotSphere();

    // Tạo các quả cầu số
    createBalls();

    // Tạo lưới hiển thị số đã quay
    createNumbersGrid();

    // Khôi phục dữ liệu từ localStorage nếu có
    loadStateFromLocalStorage();

    // Thay đổi kích thước cửa sổ
    window.addEventListener('resize', onWindowResize);

    // Bắt đầu vòng lặp animation
    animate();
}

// ===== TẠO KHUNG LƯỚI SPHERE =====
function createDotSphere() {
    const radius = sphereRadius;
    const circlePoints = 64;
    const circleMaterial = new THREE.LineBasicMaterial({
        color: 0xffffff,
        transparent: true,
        opacity: 0.8
    });

    // Vòng ngang
    for (let lat = -Math.PI / 2; lat <= Math.PI / 2; lat += Math.PI / 8) {
        const r = radius * Math.cos(lat); // Bán kính của vòng tròn tại vĩ độ này
        const y = radius * Math.sin(lat); // Tọa độ Y của vòng tròn

        // Tạo các điểm xung quanh vòng tròn
        const curve = new THREE.EllipseCurve(0, 0, r, r, 0, 2 * Math.PI);
        const points = curve.getPoints(circlePoints).map(p => new THREE.Vector3(p.x, y, p.y));

        const geometry = new THREE.BufferGeometry().setFromPoints(points);
        sphereGroup.add(new THREE.Line(geometry, circleMaterial));
    }

    // Vòng dọc
    const verticalRings = 24;
    for (let i = 0; i < verticalRings; i++) {
        const angle = (i / verticalRings) * Math.PI;
        const points = [];

        for (let j = 0; j <= circlePoints; j++) {
            const phi = (j / circlePoints) * Math.PI * 2;
            points.push(new THREE.Vector3(
                radius * Math.sin(phi) * Math.cos(angle),
                radius * Math.cos(phi),
                radius * Math.sin(phi) * Math.sin(angle)
            ));
        }

        const geometry = new THREE.BufferGeometry().setFromPoints(points);
        sphereGroup.add(new THREE.Line(geometry, circleMaterial));
    }
}

// Tạo lưới hiển thị số đã quay
function createNumbersGrid() {
    const grid = document.getElementById('numbersGrid');
    grid.innerHTML = '';
    numbersGrid = [];
}

// Thêm winner vào danh sách và tự động sắp xếp
function appendWinnerToList(number, isSpecial = false) {
    const grid = document.getElementById('numbersGrid');
    const ball = document.createElement('div');
    const label = document.createElement('span');
    label.textContent = number;
    ball.className = 'mini-ball';
    if (isSpecial) {
        // Ball thuộc lượt 5/10/15... được đánh dấu xanh trong grid bên phải.
        ball.classList.add('mini-ball-special');
    }
    ball.appendChild(label);

    ball.id = `winner-${numbersGrid.length + 1}`;
    numbersGrid.push(ball);
    grid.appendChild(ball);

    scheduleSortNumbersGrid();

    return ball;
}

let sortGridRaf = null;
function scheduleSortNumbersGrid() {
    if (sortGridRaf) return;

    sortGridRaf = requestAnimationFrame(() => {
        sortGridRaf = null;
        sortNumbersGrid();
    });
}

// Sắp xếp danh sách winners thoe hàng đơn vị, chục
function sortNumbersGrid() {
    const grid = document.getElementById('numbersGrid');
    const ballElements = Array.from(grid.querySelectorAll('.mini-ball'));
    const sorted = ballElements
        .map(ball => ({
            el: ball,
            num: parseInt(ball.querySelector('span').textContent)
        }))
        .sort((a, b) => a.num - b.num);

    const groups = new Map();
    for (const { el, num } of sorted) {
        const group = Math.floor(num / 10) * 10;
        if (!groups.has(group)) groups.set(group, []);
        groups.get(group).push(el);
    }

    const fragment = document.createDocumentFragment();
    Array.from(groups.keys())
        .sort((a, b) => a - b)
        .forEach(group => {
            const row = document.createElement('div');
            row.className = 'number-row';
            groups.get(group).forEach(el => row.appendChild(el));
            fragment.appendChild(row);
        });

    grid.replaceChildren(fragment);
}

// ===== TẠO CÁC QUẢ CẦU SỐ =====
function createBalls() {
    const sharedSphereGeometry = geometryPool.getSphereGeometry();
    const sharedPlaneGeometry1x1 = geometryPool.getPlaneGeometry(1, 1);

    preGenerateNumberTextures();

    if (!sharedSphereMaterial) {
        sharedSphereMaterial = new THREE.MeshPhongMaterial({
            color: 0xe4c47f,
            shininess: 100
        });
    }

    for (let i = 1; i <= numberOfBalls; i++) {
        const ballGroup = new THREE.Group();

        // tạo quả cầu chính
        const sphere = new THREE.Mesh(
            sharedSphereGeometry,
            sharedSphereMaterial
        );
        ballGroup.add(sphere);

        //tạo chữ số trên quả cầu
        const numberTexture = canvasTextureCache.has(i) ? canvasTextureCache.get(i) : null;
        if (!numberTexture) {
            console.error(`Failed to get texture for ball ${i}`);
            continue;
        }

        // Chuyển canvas thành texture và áp dụng lên mặt phẳng
        const textMaterial = new THREE.MeshBasicMaterial({
            map: numberTexture,
            transparent: true
        });
        const textPlane = new THREE.Mesh(sharedPlaneGeometry1x1, textMaterial);
        textPlane.position.z = 0.61;
        textPlane.renderOrder = 1;
        ballGroup.add(textPlane);

        // Đặt vị trí ngẫu nhiên
        const phi = Math.random() * Math.PI * 2;
        const theta = Math.random() * Math.PI;
        const radius = Math.random() * 4.5 + 0.5;

        // Chuyển đổi tọa độ cầu sang tọa độ Descartes
        ballGroup.position.set(
            radius * Math.sin(theta) * Math.cos(phi), // X
            radius * Math.sin(theta) * Math.sin(phi), // Y
            radius * Math.cos(theta) // Z
        );

        // Lưu thông tin quả cầu
        ballGroup.userData = {
            number: i,
            initialPos: ballGroup.position.clone(), // Vị trí ban đầu
            velocity: new THREE.Vector3( // Vận tốc ngẫu nhiên
                (Math.random() - 0.5) * 0.03,
                (Math.random() - 0.5) * 0.03,
                (Math.random() - 0.5) * 0.03
            ),
            isFalling: false,
        };

        ballsGroup.add(ballGroup);
        balls.push(ballGroup);
    }
}

// Pre-generate tất cả textures 1 lần duy nhất
function preGenerateNumberTextures() {
    if (isTexturesCached) return;

    canvasTextureCache = new Map();

    for (let i = 1; i <= numberOfBalls; i++) {
        // Tạo canvas riêng cho mỗi số
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = 256;
        canvas.height = 256;

        // Vẽ số
        ctx.font = 'bold 120px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.lineWidth = 5;
        ctx.strokeStyle = '#ffffff';
        ctx.fillStyle = '#da3b42';
        ctx.strokeText(i.toString(), 128, 128);
        ctx.fillText(i.toString(), 128, 128);
        canvasTextureCache.set(i, new THREE.CanvasTexture(canvas));
    }

    isTexturesCached = true;
}

// ===== KHUNG PHÉP TÍNH CHO LƯỢT ĐẶC BIỆT =====

// Kiểm tra ball đã được lưu là ball đặc biệt khi đưa vào grid.
function isSpecialWinnerNumber(number) {
    return specialWinnerNumbers.includes(Number(number));
}

// Lượt 5, 10, 15... sẽ hiện khung phép tính trước khi lộ ball trúng thưởng.
function shouldShowMathChallenge(drawCount) {
    return drawCount > 0 && drawCount % mathChallengeInterval === 0;
}

function getRandomItem(items) {
    return items[Math.floor(Math.random() * items.length)];
}

function isValidMathOperand(number) {
    return Number.isInteger(number) && number > 0 && number <= maxMathOperand;
}

// Tạo phép cộng 2 số a + b = ? sao cho a, b đều nhỏ hơn 70.
function createAdditionExpression(answer) {
    const candidates = [];

    for (let first = 1; first <= maxMathOperand; first++) {
        const second = answer - first;
        if (isValidMathOperand(second)) {
            candidates.push(`${first} + ${second} = ?`);
        }
    }

    return candidates.length ? getRandomItem(candidates) : null;
}

// Tạo phép trừ 2 số a - b = ? sao cho a, b đều nhỏ hơn 70.
function createSubtractionExpression(answer) {
    const candidates = [];

    for (let first = 1; first <= maxMathOperand; first++) {
        const second = first - answer;
        if (isValidMathOperand(second)) {
            candidates.push(`${first} - ${second} = ?`);
        }
    }

    return candidates.length ? getRandomItem(candidates) : null;
}

// Tạo phép nhân 2 số trong bảng cửu chương.
function createMultiplicationExpression(answer) {
    const candidates = [];

    for (let left = 2; left <= 9; left++) {
        for (let right = 2; right <= 9; right++) {
            if (left * right === answer) {
                candidates.push(`${left} x ${right} = ?`);
            }
        }
    }

    return candidates.length ? getRandomItem(candidates) : null;
}

// Tạo phép chia 2 số trong bảng cửu chương, với số bị chia vẫn nhỏ hơn 70.
function createDivisionExpression(answer) {
    const candidates = [];

    for (let divisor = 2; divisor <= 9; divisor++) {
        const dividend = answer * divisor;
        if (isValidMathOperand(dividend)) {
            candidates.push(`${dividend} ÷ ${divisor} = ?`);
        }
    }

    return candidates.length ? getRandomItem(candidates) : null;
}

// Gom tất cả phép tính 2 số hợp lệ rồi chọn ngẫu nhiên một phép để hiển thị.
function createMathExpression(answer) {
    const expressions = [
        createAdditionExpression(answer),
        createSubtractionExpression(answer),
        createMultiplicationExpression(answer),
        // createDivisionExpression(answer)
    ].filter(Boolean);

    return getRandomItem(expressions);
}

// Hiện khung phép tính và tạm dừng animation winner cho đến khi người chơi click.
function showMathChallenge(answer) {
    const challenge = document.getElementById('mathChallenge');
    const expression = document.getElementById('mathExpression');
    const button = document.getElementById('mathChallengeButton');

    isMathChallengeShowing = true;

    if (!challenge || !expression) {
        revealMathChallengeWinner();
        return;
    }

    // Reset class đóng để lần hiện mới luôn bắt đầu ở trạng thái bình thường.
    expression.textContent = createMathExpression(answer);
    challenge.classList.remove('is-closing');
    challenge.classList.add('is-visible');
    challenge.setAttribute('aria-hidden', 'false');
    if (button) {
        button.disabled = false;
    }
    isMathChallengeClosing = false;
}

// Dọn overlay phép tính sau khi animation thu nhỏ kết thúc hoặc khi reset game.
function hideMathChallenge() {
    const challenge = document.getElementById('mathChallenge');
    const expression = document.getElementById('mathExpression');

    if (challenge) {
        challenge.classList.remove('is-visible');
        challenge.classList.remove('is-closing');
        challenge.setAttribute('aria-hidden', 'true');
    }

    if (expression) {
        expression.textContent = '';
    }
}

// Khi click khung phép tính: khung thu nhỏ dần, ball được bật lại và phóng to như flow cũ.
function revealMathChallengeWinner(event) {
    if (event) {
        event.stopPropagation();
    }

    if (!isMathChallengeShowing || isMathChallengeClosing || !winnerBall) return;

    const challenge = document.getElementById('mathChallenge');
    const button = document.getElementById('mathChallengeButton');

    // Chặn click liên tục trong lúc transition thu nhỏ đang chạy.
    isMathChallengeClosing = true;
    if (challenge) {
        challenge.classList.add('is-closing');
    }
    if (button) {
        button.disabled = true;
    }

    // Bỏ trạng thái chờ để animate() tiếp tục gọi animateWinnerFalling().
    winnerBall.visible = true;
    winnerBall.userData.isWaitingForMath = false;

    // Sau transition CSS mới ẩn hẳn overlay để hiệu ứng thu nhỏ được nhìn thấy.
    setTimeout(() => {
        hideMathChallenge();
        isMathChallengeShowing = false;
        isMathChallengeClosing = false;
        if (button) {
            button.disabled = false;
        }
    }, mathChallengeCloseDuration);
}

function startSpin() {
    // Không nhận click quay mới khi đang quay, đang move ball, hoặc đang hiển thị phép tính.
    if (winnerBallMoving || isSpinning || isMathChallengeShowing) return;

    // Nếu có winner đang đứng giữa, click này sẽ đưa nó sang danh sách rồi mới quay
    if (winnerBall && winnerBall.userData.isFalling && !winnerBallMoving) {
        const cell = appendWinnerToList(winnerBall.userData.number, isSpecialWinnerNumber(winnerBall.userData.number));
        pendingSpin = true;
        //Tránh DOM write rồi read layout ngay lập tức
        //Vì appendWinnerToList() schedule sort bằng requestAnimationFrame, sau đó bạn cũng requestAnimationFrame(moveWinnerBallToCell).
        //Hai callback có thể chạy cùng frame: sort DOM xong rồi đọc getBoundingClientRect() ngay, vẫn có khả năng forced layout.
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                moveWinnerBallToCell(cell);
            });
        });

        return;
    }

    isSpinning = true;
    setSpeedStartSpin();
    setTimeout(() => {
        pickWinner();
    }, timePickWinner);
}

// Thiết lập tốc độ quay ngẫu nhiên - tăng cường độ xáo trộn
function setSpeedStartSpin() {
    rotationSpeed.y = (Math.random() + 0.5) * 1.2;
}

// ===== HÀM CHỌN QUẢ CẦU TRÚNG THƯỞNG =====
function pickWinner() {
    if (winnerBall) return;
    isSpinning = false;

    // Lọc các quả chưa rơi
    const availableBalls = balls.filter(b => !b.userData.isFalling);
    if (!availableBalls.length) return;

    // Chọn ngẫu nhiên
    const winner = availableBalls[Math.floor(Math.random() * availableBalls.length)];
    winnerBall = winner;
    winner.userData.isFalling = true;
    winner.userData.isWaitingForMath = false;

    // Get world position
    const worldPos = new THREE.Vector3();
    winner.getWorldPosition(worldPos);

    // Di chuyển ra scene
    ballsGroup.remove(winner);
    winner.position.copy(worldPos);
    scene.add(winner);

    // Rơi về trung tâm nhanh hơn
    const targetPos = new THREE.Vector3(0, 0, 5);
    winner.userData.fallVelocity = targetPos.clone().sub(worldPos).normalize().multiplyScalar(1.0);

    // Xác định lượt hiện tại có phải lượt đặc biệt 5/10/15... hay không.
    const nextDrawCount = drawnNumbers.length + 1;
    const isSpecialDraw = shouldShowMathChallenge(nextDrawCount);
    if (isSpecialDraw && !isSpecialWinnerNumber(winner.userData.number)) {
        specialWinnerNumbers.push(winner.userData.number);
    }

    drawnNumbers.push(winner.userData.number);
    saveStateToLocalStorage();

    if (isSpecialDraw) {
        // Ẩn ball cho đến khi người chơi click vào khung phép tính.
        winner.visible = false;
        winner.userData.isWaitingForMath = true;
        showMathChallenge(winner.userData.number);
    }
}

async function resetGame() {
    const confirm = window.confirm('Bạn có chắc chắn muốn Reset Game?');
    if (!confirm) return;

    try {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch('/admin/game/reset', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        });
        const data = await res.json().catch(() => ({ success: false }));

        if (data && data.success) {
            rotationSpeed.x = 0;
            rotationSpeed.y = 0;
            sphereGroup.rotation.set(0, 0, 0);
            ballsGroup.rotation.set(0, 0, 0);
            isSpinning = false;

            // Clean up floating winner element
            if (winnerFloatingElement) {
                document.body.removeChild(winnerFloatingElement);
                winnerFloatingElement = null;
            }
            hideMathChallenge();
            isMathChallengeShowing = false;
            isMathChallengeClosing = false;

            // Reset winner ball movement
            winnerBallMoving = false;
            winnerBallTarget = null;

            // Clean up any winner balls
            cleanupWinnerBalls();

            // Reset winner if exists
            if (winnerBall && winnerBall.parent) {
                winnerBall.parent.remove(winnerBall);
            }

            winnerBall = null;

            disposeAllBalls()
            clearStateInLocalStorage();
        }
    } catch (error) {
        console.error(error);
    }
}

function disposeAllBalls() {
    balls.forEach(ball => {
        if (ball.parent) {
            ball.parent.remove(ball);
        }

        disposeObject(ball);
    });

    balls.length = 0;
}
function disposeObject(obj) {
    // winnerBall
    //   ├─ sphere mesh
    //   └─ text plane mesh
    obj.traverse(child => {

        // Chỉ xử lý Mesh. Line, Group, Light... bỏ qua
        if (!child.isMesh) return;

        // =========================================================
        // KIỂM TRA GEOMETRY CÓ PHẢI DÙNG CHUNG KHÔNG
        // =========================================================

        // geometryPool.sphere: toàn bộ quả bóng đang dùng CHUNG geometry này
        // geometryPool.plane: toàn bộ text plane đang dùng CHUNG geometry này
        // Nếu dispose nhầm:
        // -> GPU buffer bị xóa
        // -> các ball còn lại bị ảnh hưởng
        // -> render bị giật / rebuild GPU
        const isSharedGeometry =
            child.geometry === geometryPool.sphere ||
            Object.values(geometryPool.plane).includes(child.geometry);

        // Chỉ dispose geometry nếu:
        // - geometry tồn tại
        // - KHÔNG phải geometry dùng chung
        // => geometry unique mới được phép dispose
        if (child.geometry && !isSharedGeometry) {
            child.geometry.dispose();
        }

        // =========================================================
        // MATERIAL
        // =========================================================

        // Có mesh dùng nhiều material
        const materials = Array.isArray(child.material)
            ? child.material
            : [child.material];

        materials.forEach(mat => {

            // material null -> bỏ qua
            if (!mat) return;

            // =====================================================
            // SHARED MATERIAL
            // =====================================================

            // sharedSphereMaterial đang được tất cả quả bóng dùng chung

            // Nếu dispose:
            // -> các quả bóng khác mất material
            // -> renderer phải rebuild shader/material
            // -> dễ gây lag GPU
            if (mat === sharedSphereMaterial) return;

            // texture của material
            const map = mat.map;

            // =====================================================
            // TEXTURE CACHE
            // =====================================================

            // canvasTextureCache chứa texture số:
            // 1 -> texture số 1
            // 2 -> texture số 2

            // Những texture này đang được cache để tái sử dụng.

            // Nếu dispose:
            // -> texture GPU bị xóa
            // -> các ball khác dùng texture đó bị ảnh hưởng
            const isCachedTexture =
                map &&
                canvasTextureCache &&
                Array.from(canvasTextureCache.values()).includes(map);

            // =====================================================
            // BACKGROUND SHARED TEXTURE
            // =====================================================

            // backgroundTexture là texture global dùng chung cho scene
            // Không được dispose giữa chừng
            const isSharedBackground =
                map === backgroundTexture;

            // =====================================================
            // DISPOSE TEXTURE UNIQUE
            // =====================================================

            // Chỉ dispose nếu:
            // - có texture
            // - là THREE.Texture
            // - KHÔNG phải texture cache
            // - KHÔNG phải background shared

            // => chỉ texture unique mới dispose
            if (
                map &&
                map.isTexture &&
                !isCachedTexture &&
                !isSharedBackground
            ) {
                map.dispose();
            }

            // =====================================================
            // DISPOSE MATERIAL
            // =====================================================

            // Material này KHÔNG phải shared material
            // => có thể dispose an toàn
            mat.dispose();
        });
    });
}

function cleanupWinnerBalls() {
    balls.forEach(ball => {
        if (ball.userData.isFalling) {
            scene.remove(ball);
            ballsGroup.add(ball);
            ball.position.copy(ball.userData.initialPos);
            ball.scale.set(1, 1, 1);
            ball.rotation.set(0, 0, 0);
            ball.userData.isFalling = false;
            ball.userData.velocity.set(
                (Math.random() - 0.5) * 0.03,
                (Math.random() - 0.5) * 0.03,
                (Math.random() - 0.5) * 0.03
            );
        }
    });
}

function animate() {
    // Lên lịch frame tiếp theo
    requestAnimationFrame(animate);

    if (isSpinning) {
        // Quay khung lưới & nhóm quả cầu
        sphereRotation();
        // Cập nhật chuyển động vật lý của các quả cầu
        ballMotionWhileSpinning();
    } else if (!winnerBall) {
        // Hiệu ứng quay nhẹ khi chờ
        idleMotion();
    }

    // ===== XỬ LÝ ANIMATION QUẢ CẦU TRÚNG THƯỞNG =====
    if (winnerBall && winnerBall.userData.isFalling && !winnerBall.userData.isWaitingForMath) {
        if (!winnerBallMoving) {
            // quả cầu rơi về giữa
            animateWinnerFalling();
        } else {
            // di chuyển đến grid
            animateWinnerMoveToGrid();
        }

        // Khi winner, sphere vẫn quay nhẹ
        sphereGroup.rotation.y += 0.01;
        ballsGroup.rotation.y += 0.01;
    }

    // Giúp các quả cầu luôn hướng về camera
    handleBallFacingCamera();

    renderer.render(scene, camera);

}

// Quay khối cầu và nhóm quả cầu trong khi quay
function sphereRotation() {
    // Sphere chỉ xoay trục Y
    sphereGroup.rotation.y += rotationSpeed.y;

    // Balls xoay cả 3 trục để tạo cảm giác động hơn
    ballsGroup.rotation.x += rotationSpeed.x * 0.3;
    ballsGroup.rotation.y += rotationSpeed.y * 0.5;
    ballsGroup.rotation.z += rotationSpeed.x * 0.3;

    // Giảm tốc độ quay mượt hơn bằng cách tiến dần về 0
    const easing = 0.03; // giá trị càng nhỏ thì giảm càng chậm
    rotationSpeed.x = THREE.MathUtils.lerp(rotationSpeed.x, 0, easing);
    rotationSpeed.y = THREE.MathUtils.lerp(rotationSpeed.y, 0, easing);
}

// Cập nhật chuyển động của từng quả cầu khi đang quay
const collisionNormal = new THREE.Vector3();
function ballMotionWhileSpinning() {
    const maxRadius = 5.2;    // Bán kính tối đa - khung chứa của quả cầu
    const maxSpeed = 0.5;     // Tốc độ tối đa cho phép của mỗi quả bóng
    const randomChance = 0.6; // Xác suất 60% để áp dụng dao động ngẫu nhiên

    balls.forEach(ball => {
        if (ball.userData.isFalling) return;

        ball.position.add(ball.userData.velocity);

        // Kiểm tra va chạm với biên sphere
        const distance = ball.position.length();
        if (distance > maxRadius) {
            collisionNormal.copy(ball.position).normalize();
            ball.userData.velocity.reflect(collisionNormal);

            ball.userData.velocity.multiplyScalar(0.95);
            ball.position.setLength(maxRadius);
        }

        // Thêm chuyển động hỗn loạn ngẫu nhiên (giảm tần suất để tối ưu)
        if (Math.random() < randomChance) {
            const randomFactor = (Math.random() - 0.5) * 0.08;
            ball.userData.velocity.x += randomFactor;
            ball.userData.velocity.y += randomFactor;
            ball.userData.velocity.z += randomFactor;
        }

        // Giới hạn vận tốc tối đa - tăng để xáo trộn nhiều hơn
        const speed = ball.userData.velocity.length();
        if (speed > maxSpeed) {
            ball.userData.velocity.setLength(maxSpeed);
        }
    });
}

// Hiệu ứng quay nhẹ và chuyển động chậm khi nghỉ
function idleMotion() {
    // Quay nhẹ nhàng khi nghỉ (chỉ trục Y)
    sphereGroup.rotation.y += 0.008;
    ballsGroup.rotation.y += 0.008;

    const maxRadius = 5.2;

    // Chuyển động nhẹ nhàng của quả cầu khi nghỉ
    balls.forEach(ball => {
        if (ball.userData.isFalling) return;

        ball.position.add(ball.userData.velocity);

        // Kiểm tra va chạm với biên (tương tự khi quay)
        const distance = ball.position.length();
        if (distance > maxRadius) {
            const normal = ball.position.clone().normalize();
            ball.userData.velocity.reflect(normal);
            ball.userData.velocity.multiplyScalar(0.85);
            ball.position.setLength(maxRadius);
        }
    });
}

// Quả cầu trúng thưởng rơi về giữa màn hình
function animateWinnerFalling() {
    // Di chuyển quả bóng theo velocity hiện tại
    // Tổng movement mỗi frame = 0.5
    winnerBall.position.add(winnerBall.userData.fallVelocity);

    // Target nằm giữa màn hình và phía trước camera 8 units
    winnerTargetPos.set(0, 0, camera.position.z - 8);

    const speedFalling = 0.25;

    if (!winnerBall.userData.targetDirection) {
        // Vector hướng từ vị trí hiện tại → target
        // normalize() để bay đúng hướng, với tốc độ cố định
        winnerBall.userData.targetDirection = new THREE.Vector3()
            .subVectors(winnerTargetPos, winnerBall.position)
            .normalize();

        // Tạo vận tốc cố định theo direction đã tính
        // 0.5 = tốc độ di chuyển mỗi frame
        winnerBall.userData.fallVelocity = new THREE.Vector3()
            .copy(winnerBall.userData.targetDirection)
            .multiplyScalar(speedFalling);
    }

    winnerBall.position.add(winnerBall.userData.fallVelocity);

    // Phóng to quả cầu dần (tối đa 3 lần)
    const scale = Math.min(winnerBall.scale.x + speedFalling, 3);
    winnerBall.scale.setScalar(scale);

    // Kiểm tra đã đến giữa màn hình chưa
    if (winnerBall.position.distanceToSquared(winnerTargetPos) < 0.25) {
        winnerBall.position.copy(winnerTargetPos);
        winnerBall.userData.fallVelocity.set(0, 0, 0);
        winnerBall.userData.targetDirection = null;

        if (winnerBall.userData.centerScale === undefined) {
            winnerBall.userData.centerScale = winnerBall.scale.x;
            winnerBall.userData.pulsePhase = 0;
        }
    }
}

// Quả cầu trúng thưởng di chuyển đến vị trí grid
function animateWinnerMoveToGrid() {
    if (!winnerBallTarget) return;

    winnerBall.userData.moveProgress += winnerBall.userData.moveSpeed;
    if (winnerBall.userData.moveProgress < 1) {
        // Di chuyển mượt mà với hiệu ứng easing
        const t = winnerBall.userData.moveProgress;
        const easedT = t * t * (3 - 2 * t);

        // Nội suy vị trí từ giữa màn hình đến vị trí grid
        winnerBall.position.lerpVectors(
            winnerMoveStartPos, // Vị trí bắt đầu (giữa màn hình)
            winnerBallTarget, // Vị trí đích (grid)
            easedT // Tỷ lệ nội suy
        );

        // Thu nhỏ quả cầu khi di chuyển (từ 3 xuống 0.5)
        const scale = 3 - (t * 2.5);
        winnerBall.scale.set(scale, scale, scale);
    } else {
        // hoàng thành di chuyển
        winnerBallMoving = false;
        winnerBallTarget = null;

        // Xóa quả cầu khỏi scene và dispose resources
        scene.remove(winnerBall);
        disposeObject(winnerBall);

        // Remove from balls array efficiently
        const winnerIndex = balls.indexOf(winnerBall);
        if (winnerIndex > -1) {
            balls.splice(winnerIndex, 1);
        }

        winnerBall = null;

        // Nếu người dùng vừa click yêu cầu quay tiếp, bắt đầu quay sau khi di chuyển xong
        if (pendingSpin) {
            pendingSpin = false;
            isSpinning = true;
            setSpeedStartSpin();
            setTimeout(() => {
                pickWinner();
            }, timePickWinner);
        }
    }
}

//Xử lý để tất cả quả cầu luôn hướng về camera
function handleBallFacingCamera() {
    // Cache camera position to avoid repeated calculations
    const cameraPos = camera.position;

    // Only update balls that are visible and not falling (reduce lookAt calls)
    balls.forEach(ball => {
        if (!ball.userData.isFalling && ball.visible) {
            ball.lookAt(cameraPos);
        }
    });

    // Làm quả cầu trúng thưởng hướng về camera và không xoay theo sphere
    if (winnerBall && winnerBall.userData.isFalling) {
        winnerBall.lookAt(cameraPos);
    }
}

function saveStateToLocalStorage() {
    // Lưu song song: danh sách đã quay và danh sách cần tô xanh sau khi reload.
    localStorage.setItem('drawn_numbers', JSON.stringify(drawnNumbers));
    localStorage.setItem('special_winner_numbers', JSON.stringify(specialWinnerNumbers));
}

function clearStateInLocalStorage() {
    // Reset game thì xóa cả trạng thái quay thường và trạng thái ball đặc biệt.
    localStorage.removeItem('drawn_numbers');
    localStorage.removeItem('special_winner_numbers');
    location.reload();
}

function loadStateFromLocalStorage() {
    try {
        const rawDrawnNumbers = localStorage.getItem('drawn_numbers');
        const rawSpecialWinnerNumbers = localStorage.getItem('special_winner_numbers');
        const restored = rawDrawnNumbers ? JSON.parse(rawDrawnNumbers) : [];
        const restoredSpecial = rawSpecialWinnerNumbers ? JSON.parse(rawSpecialWinnerNumbers) : [];

        // Lọc hợp lệ
        drawnNumbers = restored.filter(n => Number.isInteger(n) && n >= 1 && n <= numberOfBalls);
        specialWinnerNumbers = restoredSpecial.filter(n => Number.isInteger(n) && n >= 1 && n <= numberOfBalls);
        // Render lại grid, ball nào nằm trong specialSet thì nhận class xanh.
        const drawnSet = new Set(drawnNumbers.map(Number));
        const specialSet = new Set(specialWinnerNumbers.map(Number));
        for (const n of drawnSet) {
            appendWinnerToList(n, specialSet.has(n));
        }

        // Lọc balls: nếu số của quả bóng có trong drawnSet thì xóa khỏi scene và balls
        balls = balls.filter(b => {
            const num = Number(b.userData?.number);
            if (drawnSet.has(num)) {
                // Gỡ khỏi scene và group
                ballsGroup.remove(b);
                scene.remove(b);
                return false; // loại bỏ khỏi mảng balls
            }
            return true; // giữ lại
        });

    } catch (error) {
        console.error(error);
    }
}

// Hàm này dùng để cập nhật lại kích thước của renderer (vùng hiển thị 3D)
function updateRendererSize() {
    const container = document.getElementById('container');
    const containerWidth = container.clientWidth;
    const containerHeight = container.clientHeight;

    renderer.setSize(containerWidth, containerHeight);
    camera.aspect = containerWidth / containerHeight;

    // Sau khi thay đổi aspect ratio, cần gọi phương thức này để áp dụng thay đổi vào ma trận chiếu (projection matrix)
    camera.updateProjectionMatrix();
    // sphere tự co giãn theo màn LED
    fitCameraToSphere(sphereRadius);
}

function fitCameraToSphere(radius) {
    //Chừa khoảng trống quanh sphere. Tăng lên thì sphere nhỏ hơn, giảm xuống thì sphere to hơn.
    const padding = 1.1;

    //Lấy góc nhìn dọc của camera.
    const vFov = THREE.MathUtils.degToRad(camera.fov);
    //Tính góc nhìn ngang theo tỉ lệ màn hình.
    const hFov = 2 * Math.atan(Math.tan(vFov / 2) * camera.aspect);
    //Lấy góc nhỏ hơn để đảm bảo sphere không bị cắt theo chiều hẹp nhất.
    const limitedFov = Math.min(vFov, hFov);
    //Tính camera phải đứng xa bao nhiêu để nhìn trọn sphere.
    const distance = (radius * padding) / Math.sin(limitedFov / 2);
    //Đặt camera ra xa/gần tương ứng và nhìn về tâm.
    camera.position.z = distance;
    camera.lookAt(0, 0, 0);
}

let resizeRaf = null;
function onWindowResize() {
    if (resizeRaf) return;
    resizeRaf = requestAnimationFrame(() => {
        resizeRaf = null;
        updateRendererSize();
    });
}

//Di chuyển quả cầu đến một ô trong danh sách ball
function moveWinnerBallToCell(targetCell) {
    if (!winnerBall || !targetCell || winnerBallMoving) return;

    winnerBallMoving = true;

    // Lấy vị trí của ô đích và container trên màn hình
    const targetRect = targetCell.getBoundingClientRect();
    const containerRect = document.getElementById('container').getBoundingClientRect();

    // Chuyển toạ độ màn hình sang toạ độ 3D (World Coordinates)
    const x = ((targetRect.left + targetRect.width / 2 - containerRect.left) / containerRect.width) * 2 - 1;
    const y = -((targetRect.top + targetRect.height / 2 - containerRect.top) / containerRect.height) * 2 + 1;

    // Tạo vị trí mục tiêu trong không gian 3D
    winnerBallTarget = new THREE.Vector3(x * 8, y * 6, 5);

    // Cài đặt thông số di chuyển cho bóng (tốc độ nhanh hơn)
    winnerBall.userData.moveSpeed = 0.05;
    winnerBall.userData.moveProgress = 0;
}
