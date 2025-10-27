import logoUrl from '../../../images/thk_logo.png';
import backgroundUrl from '../../../images/background_bingo.png';

document.addEventListener('DOMContentLoaded', () => {
    init();
    document.getElementById('container').addEventListener('click', () => {
        if (!isSpinning && !winnerBallMoving) {
            startSpin();
        }
    });

    const resetBtn = document.querySelector('.btn-reset');
    resetBtn.addEventListener('click', () => {
        resetGame();
    });
});

let scene, camera, renderer, sphereGroup, ballsGroup; // Các đối tượng Three.js chính
let balls = []; // Mảng chứa tất cả quả cầu số
let rotationSpeed = {
    x: 0,
    y: 0
}; // Tốc độ quay của sphere và balls
let isSpinning = false; // Trạng thái đang quay hay không
let winnerBall = null; // Quả cầu trúng thưởng hiện tại
let drawnNumbers = []; // Mảng lưu các số đã quay
let numbersGrid = []; // Mảng lưu các ô số trong grid
let winnerFloatingElement = null; // Element hiển thị số trúng thưởng
let winnerBallMoving = false; // Trạng thái quả cầu đang di chuyển đến grid
let winnerBallTarget = null; // Vị trí đích của quả cầu trúng thưởng
let numberOfBalls = 50 // Tổng số quả cầu hiển thị
let pendingSpin = false; // Đánh dấu người dùng đã click để quay tiếp sau khi di chuyển winner
let lastCleanup = 0; // Thời gian cleanup cuối cùng
const CLEANUP_INTERVAL = 5000; // Cleanup mỗi 5 giây

// Logo texture (gắn lên mỗi quả bóng)
const textureLoader = new THREE.TextureLoader();
const logoTexture = textureLoader.load(logoUrl);
const backgroundTexture = textureLoader.load(backgroundUrl);

const canvasTextureCache = new Map(); // Cache texture theo số (1-50)
let isTexturesCached = false;


const geometryPool = {
    sphere: null,
    plane: {},

    getSphereGeometry() {
        if (!this.sphere) {
            this.sphere = new THREE.SphereGeometry(0.6, 32, 32);
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
    // Tạo scene 3D với nền đen
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

    // Ánh sáng điểm 2 - chiếu sáng phụ từ góc trái dưới
    const pointLight2 = new THREE.PointLight(0xffffff, 0.5, 100);
    pointLight2.position.set(-10, -10, 5);
    scene.add(pointLight2);

    // ===== TẠO CÁC NHÓM ĐỐI TƯỢNG =====
    // Nhóm chứa khung lưới sphere
    sphereGroup = new THREE.Group();
    scene.add(sphereGroup);

    // Nhóm chứa các quả cầu số (bên trong sphere)
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
    // Bán kính của sphere
    const radius = 6.5;
    // Số điểm để tạo đường tròn mượt mà
    const circlePoints = 128;

    // Tạo vật liệu cho các đường viền - màu trắng với độ trong suốt
    const circleMaterial = new THREE.LineBasicMaterial({
        color: 0xffffff, // Màu trắng
        linewidth: 3, // Độ dày đường viền
        transparent: true, // Cho phép trong suốt
        opacity: 0.8 // Độ trong suốt 80%
    });

    // ===== TẠO CÁC VÒNG NGANG =====
    // Tạo các vòng tròn ngang từ -90° đến +90° (từ dưới lên trên)
    for (let lat = -Math.PI / 2; lat <= Math.PI / 2; lat += Math.PI / 8) {
        const points = []; // Mảng chứa các điểm của vòng tròn
        const r = radius * Math.cos(lat); // Bán kính của vòng tròn tại vĩ độ này
        const y = radius * Math.sin(lat); // Tọa độ Y của vòng tròn

        // Tạo các điểm xung quanh vòng tròn
        for (let i = 0; i <= circlePoints; i++) {
            const angle = (i / circlePoints) * Math.PI * 2; // Góc từ 0 đến 2π
            points.push(new THREE.Vector3(
                r * Math.cos(angle), // Tọa độ X
                y, // Tọa độ Y (cố định cho vòng tròn)
                r * Math.sin(angle) // Tọa độ Z
            ));
        }

        // Tạo geometry và line từ các điểm
        const geometry = new THREE.BufferGeometry().setFromPoints(points);
        const circle = new THREE.Line(geometry, circleMaterial);
        sphereGroup.add(circle); // Thêm vào nhóm sphere
    }

    // ===== TẠO CÁC VÒNG DỌC =====
    // Tạo 24 vòng tròn dọc xung quanh sphere
    for (let i = 0; i < 24; i++) {
        const points = []; // Mảng chứa các điểm của vòng tròn
        const angle = (i / 24) * Math.PI; // Góc quay của vòng tròn dọc

        // Tạo các điểm xung quanh vòng tròn dọc
        for (let j = 0; j <= circlePoints; j++) {
            const phi = (j / circlePoints) * Math.PI * 2; // Góc từ 0 đến 2π
            points.push(new THREE.Vector3(
                radius * Math.sin(phi) * Math.cos(angle), // Tọa độ X
                radius * Math.cos(phi), // Tọa độ Y
                radius * Math.sin(phi) * Math.sin(angle) // Tọa độ Z
            ));
        }

        // Tạo geometry và line từ các điểm
        const geometry = new THREE.BufferGeometry().setFromPoints(points);
        const circle = new THREE.Line(geometry, circleMaterial);
        sphereGroup.add(circle); // Thêm vào nhóm sphere
    }
}

// ===== TẠO LƯỚI HIỂN THỊ SỐ ĐÃ QUAY =====
function createNumbersGrid() {
    const grid = document.getElementById('numbersGrid');
    grid.innerHTML = '';
    numbersGrid = [];
}

/**
 * Thêm winner vào danh sách và tự động sắp xếp
 */
function appendWinnerToList(number) {
    const grid = document.getElementById('numbersGrid');
    const ball = document.createElement('div');
    const label = document.createElement('span');
    label.textContent = number;
    ball.className = 'mini-ball';
    ball.appendChild(label);

    ball.id = `winner-${numbersGrid.length + 1}`;
    numbersGrid.push(ball);
    grid.appendChild(ball);

    sortNumbersGrid()

    return ball;
}


/**
 * Sắp xếp danh sách winners theo thứ tự số tăng dần
 */
function sortNumbersGrid() {
    const grid = document.getElementById('numbersGrid');

    // Lấy tất cả các ball elements và số của chúng
    const ballElements = Array.from(grid.children);
    const ballData = ballElements.map(ball => ({
        element: ball,
        number: parseInt(ball.querySelector('span').textContent)
    }));

    // Sắp xếp theo số tăng dần
    ballData.sort((a, b) => a.number - b.number);

    // Xóa tất cả và thêm lại theo thứ tự
    grid.innerHTML = '';
    ballData.forEach(item => {
        grid.appendChild(item.element);
    });
}

// ===== TẠO CÁC QUẢ CẦU SỐ =====
function createBalls() {
    // Pre-generate tất cả textures TRƯỚC KHI tạo balls
    preGenerateNumberTextures();

    // Lấy shared geometries
    const sharedSphereGeometry = geometryPool.getSphereGeometry();
    const sharedPlaneGeometry1x1 = geometryPool.getPlaneGeometry(1, 1);
    const sharedLogoPlaneGeometry = geometryPool.getPlaneGeometry(0.45, 0.5);

    for (let i = 1; i <= numberOfBalls; i++) {
        const ballGroup = new THREE.Group(); // Tạo nhóm chứa quả cầu và chữ số

        // ===== TẠO QUẢ CẦU CHÍNH =====
        const sphere = new THREE.Mesh(
            sharedSphereGeometry, // Hình cầu bán kính 0.6, 32x32 segments
            new THREE.MeshPhongMaterial({
                color: 0xe4c47f, // Màu vàng nhạt
                shininess: 100 // Độ bóng cao
            })
        );
        ballGroup.add(sphere);

        //tạo chữ số trên quả cầu
        const numberTexture = getNumberTexture(i);
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
        textPlane.position.z = 0.61; // Đặt sát bề mặt quả cầu
        textPlane.renderOrder = 1; // dưới logo
        ballGroup.add(textPlane); // Thêm mặt phẳng chữ số vào nhóm

        // thêm logo
        const logoMaterial = new THREE.MeshBasicMaterial({
            map: logoTexture,
            transparent: true, // dùng alpha
            depthTest: true, // vẫn bị quả bóng che khi quay ra sau
            depthWrite: false
        });

        // Logo nổi phía trước con số, không dính vào bề mặt quả bóng
        const logoPlane = new THREE.Mesh(sharedLogoPlaneGeometry, logoMaterial);
        logoPlane.position.set(0, 0.3, 0.7); // đẩy ra trước số rõ ràng
        logoPlane.renderOrder = 2; // trên số
        ballGroup.add(logoPlane);

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
            number: i, // Số trên quả cầu
            initialPos: ballGroup.position.clone(), // Vị trí ban đầu
            velocity: new THREE.Vector3( // Vận tốc ngẫu nhiên
                (Math.random() - 0.5) * 0.03,
                (Math.random() - 0.5) * 0.03,
                (Math.random() - 0.5) * 0.03
            ),
            isFalling: false, // Trạng thái không rơi
        };

        ballsGroup.add(ballGroup); // Thêm vào nhóm balls
        balls.push(ballGroup); // Thêm vào mảng balls
    }
}

/**
 * Pre-generate tất cả textures 1 lần duy nhất
 * Tránh memory leak từ việc tạo canvas liên tục
 */
function preGenerateNumberTextures(number) {
    if (isTexturesCached) return;

    console.log('Pre-generating number textures...');
    for (let i = 1; i <= numberOfBalls; i++) {
        // Tạo canvas RIÊNG cho mỗi số
        const canvas = document.createElement('canvas');
        canvas.width = 256;
        canvas.height = 256;
        const ctx = canvas.getContext('2d');

        // Vẽ số
        ctx.font = 'bold 120px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.lineWidth = 5;
        ctx.strokeStyle = '#ffffff';
        ctx.fillStyle = '#da3b42';
        ctx.strokeText(i.toString(), 128, 128);
        ctx.fillText(i.toString(), 128, 128);

        // Tạo texture từ canvas
        const texture = new THREE.CanvasTexture(canvas);
        texture.needsUpdate = true;

        // Lưu vào cache
        canvasTextureCache.set(i, texture);
    }

    isTexturesCached = true;
    console.log(`Pre-generated ${canvasTextureCache.size} textures`);
}

function getNumberTexture(number) {
    if (!isTexturesCached) {
        preGenerateNumberTextures();
    }

    if (canvasTextureCache.has(number)) {
        return canvasTextureCache.get(number);
    }

    console.error(`Texture for number ${number} not found in cache!`);
    return null;
}

// ===== HÀM BẮT ĐẦU QUAY SỐ =====
function startSpin() {
    // Nếu đang di chuyển sang danh sách thì không xử lý
    // Ngăn chặn quay nhiều lần cùng lúc
    if (winnerBallMoving || isSpinning) return;

    // Nếu có winner đang đứng giữa, click này sẽ đưa nó sang danh sách rồi mới quay
    if (winnerBall && winnerBall.userData.isFalling && !winnerBallMoving) {
        const cell = appendWinnerToList(winnerBall.userData.number);
        pendingSpin = true;
        moveWinnerBallToCell(cell);
        return;
    }

    isSpinning = true;
    startNewSpin();
}

// ===== HÀM BẮT ĐẦU QUAY MỚI =====
function startNewSpin() {
    // Thiết lập tốc độ quay ngẫu nhiên - tăng cường độ xáo trộn
    rotationSpeed.x = (Math.random() - 0.5) * 1.2;
    rotationSpeed.y = (Math.random() - 0.5) * 1.0;

    // Dừng quay sau 3 giây và chọn quả cầu trúng thưởng
    setTimeout(() => {
        pickWinner();
    }, 3000);
}

// ===== HÀM CHỌN QUẢ CẦU TRÚNG THƯỞNG =====
function pickWinner() {
    if (winnerBall) return;
    isSpinning = false;

    // ===== CHỌN NGẪU NHIÊN 1 QUẢ CẦU KHÔNG ĐANG RƠI =====
    const candidates = balls.filter(ball => !ball.userData.isFalling);
    if (candidates.length === 0) return;
    const winner = candidates[Math.floor(Math.random() * candidates.length)];

    if (winner) {
        winnerBall = winner;
        winner.userData.isFalling = true;

        // Get world position
        const worldPos = new THREE.Vector3();
        winner.getWorldPosition(worldPos);

        // Get world rotation
        const worldQuaternion = new THREE.Quaternion();
        winner.getWorldQuaternion(worldQuaternion);

        // Remove from group and add to scene
        ballsGroup.remove(winner);
        winner.position.copy(worldPos);
        winner.quaternion.copy(worldQuaternion);
        scene.add(winner);

        // Set fall velocity toward center of screen - faster
        const targetPos = new THREE.Vector3(0, 0, 5);
        const direction = targetPos.clone().sub(worldPos).normalize();
        winner.userData.fallVelocity = direction.multiplyScalar(0.25);
        winner.userData.rotationSpeed = {
            x: Math.random() * 0.2 - 0.1,
            y: Math.random() * 0.2 - 0.1,
            z: Math.random() * 0.2 - 0.1
        };

        // Thêm vào drawn numbers và hiển thị vào danh sách bên phải
        drawnNumbers.push(winner.userData.number);
        saveStateToLocalStorage();
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

            // Reset winner ball movement
            winnerBallMoving = false;
            winnerBallTarget = null;

            // Clean up any winner balls
            cleanupWinnerBalls();

            // Reset winner if exists
            if (winnerBall) {
                scene.remove(winnerBall);
                disposeObject(winnerBall);
                ballsGroup.add(winnerBall);
                winnerBall.position.copy(winnerBall.userData.initialPos);
                winnerBall.scale.set(1, 1, 1);
                winnerBall.rotation.set(0, 0, 0);
                winnerBall.userData.isFalling = false;

                // Reset velocity
                winnerBall.userData.velocity.set(
                    (Math.random() - 0.5) * 0.03,
                    (Math.random() - 0.5) * 0.03,
                    (Math.random() - 0.5) * 0.03
                );

                winnerBall = null;
            }

            disposeAllBalls()
            clearStateInLocalStorage();
        }
    } catch (error) {
        console.error(error);
    }
}

function disposeAllBalls() {
    balls.forEach(ball => {
        scene.remove(ball);
        ball.traverse(child => disposeObject(child));
    });
    balls.length = 0;
}

function disposeObject(obj) {
    obj.traverse(child => {
        if (child.isMesh) {
            if (child.geometry) child.geometry.dispose();

            if (child.material) {
                const materials = Array.isArray(child.material) ? child.material : [child.material];
                materials.forEach(mat => {
                    if (mat.map && mat.map.isTexture) {
                        const isFromCache = Array.from(canvasTextureCache.values()).includes(mat.map);
                        const isSharedTexture = (mat.map === logoTexture || mat.map === backgroundTexture);

                        if (!isFromCache && !isSharedTexture) {
                            // Chỉ dispose nếu KHÔNG phải texture từ cache
                            if (mat.map.source && mat.map.source.data) {
                                // This is a canvas texture - clear the canvas
                                const canvas = mat.map.source.data;
                                if (canvas && canvas.getContext) {
                                    const ctx = canvas.getContext('2d');
                                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                                }
                            }
                            mat.map.dispose();
                        }

                    }

                    // Dispose additional texture maps
                    if (mat.normalMap) mat.normalMap.dispose();
                    if (mat.bumpMap) mat.bumpMap.dispose();
                    if (mat.specularMap) mat.specularMap.dispose();
                    if (mat.emissiveMap) mat.emissiveMap.dispose();
                    if (mat.alphaMap) mat.alphaMap.dispose();
                    mat.dispose();
                });
            }
        }
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

    const now = performance.now();
    if (!animate.lastTime) animate.lastTime = now;
    const deltaSeconds = (now - animate.lastTime) / 1000;
    animate.lastTime = now;

    // Performance monitoring - skip frame if delta is too high
    if (deltaSeconds > 0.1) {
        console.warn('Animation lag detected:', deltaSeconds);
        return;
    }

    if (!animate.frameCount) animate.frameCount = 0;
    animate.frameCount++;

    // ===== XỬ LÝ KHI ĐANG QUAY =====
    if (isSpinning) {
        // Quay khung lưới & nhóm quả cầu
        updateSphereRotation();
        // Cập nhật chuyển động vật lý của các quả cầu
        updateBallMotionWhileSpinning();
    }

    // ===== XỬ LÝ KHI KHÔNG QUAY VÀ KHÔNG CÓ QUẢ CẦU TRÚNG THƯỞNG =====
    else if (!winnerBall) {
        // Hiệu ứng quay nhẹ khi chờ
        idleMotion();
    }

    // ===== XỬ LÝ ANIMATION QUẢ CẦU TRÚNG THƯỞNG =====
    if (winnerBall && winnerBall.userData.isFalling) {
        if (!winnerBallMoving) {
            // quả cầu rơi về giữa
            animateWinnerFalling();
        } else {
            // di chuyển đến grid
            animateWinnerMoveToGrid();
        }
        // Trong khi winner đang di chuyển, sphere vẫn quay nhẹ
        sphereGroup.rotation.y += 0.01;
        ballsGroup.rotation.y += 0.01;
    }

    // Giúp các quả cầu luôn hướng về camera
    handleBallFacingCamera();

    // Periodic cleanup to prevent memory leaks - chỉ check mỗi 60 frames (~1s)
    if (animate.frameCount % 60 === 0) {
        if (now - lastCleanup > CLEANUP_INTERVAL) {
            performPeriodicCleanup();
            lastCleanup = now;
        }
    }

    // Vẽ scene lên màn hình
    renderer.render(scene, camera);
}

// Quay khối cầu và nhóm quả cầu trong khi quay
function updateSphereRotation() {
    // Sphere chỉ xoay trục Y
    sphereGroup.rotation.y += rotationSpeed.y;

    // Balls xoay cả 2 trục để xáo trộn nhiều hơn
    ballsGroup.rotation.x += rotationSpeed.x * 1.5; // Tăng tốc độ xoay X
    ballsGroup.rotation.y += rotationSpeed.y;
    ballsGroup.rotation.z += rotationSpeed.x * 0.8; // Thêm xoay trục Z

    // Giảm tốc độ quay dần (hiệu ứng chậm lại)
    rotationSpeed.x *= 0.98;
    rotationSpeed.y *= 0.98;
}

// Cập nhật chuyển động của từng quả cầu khi đang quay
function updateBallMotionWhileSpinning() {
    // Chỉ xử lý quả cầu đang không rơi
    const activeBalls = balls.filter(ball => !ball.userData.isFalling);
    activeBalls.forEach(ball => {
        ball.position.add(ball.userData.velocity); // Cập nhật vị trí theo vận tốc

        // Kiểm tra va chạm với biên sphere
        const distance = ball.position.length();
        if (distance > 5.2) { // Nếu vượt quá bán kính cho phép
            const normal = ball.position.clone().normalize(); // Vector pháp tuyến
            ball.userData.velocity.reflect(normal); // Phản xạ vận tốc
            ball.userData.velocity.multiplyScalar(0.95); // Giảm vận tốc 5%
            ball.position.setLength(5.2); // Đặt lại vị trí về biên
        }

        // Thêm chuyển động hỗn loạn ngẫu nhiên (giảm tần suất để tối ưu)
        if (Math.random() < 0.3) {
            const randomFactor = (Math.random() - 0.5) * 0.05;
            ball.userData.velocity.x += randomFactor;
            ball.userData.velocity.y += randomFactor;
            ball.userData.velocity.z += randomFactor;
        }

        // Thêm xoay ngẫu nhiên cho từng quả cầu (giảm tần suất)
        if (Math.random() < 0.2) { // Chỉ 10% khả năng xoay
            const rotationFactor = (Math.random() - 0.5) * 0.1;
            ball.rotation.x += rotationFactor;
            ball.rotation.y += rotationFactor;
            ball.rotation.z += rotationFactor;
        }

        // Giới hạn vận tốc tối đa - tăng để xáo trộn nhiều hơn
        const speed = ball.userData.velocity.length();
        if (speed > 0.5) {
            ball.userData.velocity.setLength(0.5);
        }
    });
}

// Hiệu ứng quay nhẹ và chuyển động chậm khi nghỉ
function idleMotion() {
    // Quay nhẹ nhàng khi nghỉ (chỉ trục Y)
    sphereGroup.rotation.y += 0.008;
    ballsGroup.rotation.y += 0.008;

    // Chuyển động nhẹ nhàng của quả cầu khi nghỉ
    balls.forEach(ball => {
        if (!ball.userData.isFalling) {
            ball.position.add(ball.userData.velocity); // Cập nhật vị trí

            // Kiểm tra va chạm với biên (tương tự khi quay)
            const distance = ball.position.length();
            if (distance > 5.2) {
                const normal = ball.position.clone().normalize();
                ball.userData.velocity.reflect(normal);
                ball.userData.velocity.multiplyScalar(0.85); // Giảm vận tốc 15%
                ball.position.setLength(5.2);
            }
        }
    });
}

// Quả cầu trúng thưởng rơi về giữa màn hình
function animateWinnerFalling() {
    // Cập nhật vị trí
    winnerBall.position.add(winnerBall.userData.fallVelocity);

    // Di chuyển về giữa màn hình (tọa độ 0,0,6) gần camera
    const targetPos = new THREE.Vector3(0, 0, camera.position.z - 8);
    if (!winnerBall.userData.targetDirection) {
        winnerBall.userData.targetDirection = targetPos.clone().sub(winnerBall.position).normalize();
    }
    // const direction = winnerBall.userData.targetDirection.clone().sub(winnerBall.position).normalize();
    // Di chuyển với tốc độ cố định theo hướng đã tính
    winnerBall.userData.fallVelocity = winnerBall.userData.targetDirection.clone().multiplyScalar(0.15);
    winnerBall.position.add(winnerBall.userData.fallVelocity);

    // Phóng to quả cầu dần (tối đa 3 lần)
    const scale = Math.min(winnerBall.scale.x + 0.04, 3);
    winnerBall.scale.set(scale, scale, scale);

    // Giữ quả cầu hướng về camera (không xoay)
    winnerBall.rotation.set(0, 0, 0);

    // Kiểm tra đã đến giữa màn hình chưa
    if (winnerBall.position.distanceTo(targetPos) < 0.5) {
        winnerBall.position.copy(targetPos);
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

    // Tăng tiến độ
    winnerBall.userData.moveProgress += winnerBall.userData.moveSpeed;

    if (winnerBall.userData.moveProgress < 1) {
        // Di chuyển mượt mà với hiệu ứng easing
        const t = winnerBall.userData.moveProgress; // Tiến độ 0-1
        const easedT = t * t * (3 - 2 * t); // Smooth step easing

        // Nội suy vị trí từ giữa màn hình đến vị trí grid
        winnerBall.position.lerpVectors(
            new THREE.Vector3(0, 0, 5), // Vị trí bắt đầu (giữa màn hình)
            winnerBallTarget, // Vị trí đích (grid)
            easedT // Tỷ lệ nội suy
        );

        // Thu nhỏ quả cầu khi di chuyển (từ 3 xuống 0.5)
        const scale = 3 - (t * 2.5);
        winnerBall.scale.set(scale, scale, scale);
    } else {
        // ===== HOÀN THÀNH DI CHUYỂN =====
        winnerBallMoving = false;
        winnerBallTarget = null;

        // Cập nhật grid và xóa quả cầu
        const number = winnerBall.userData.number;

        // Xóa quả cầu khỏi scene và dispose resources
        scene.remove(winnerBall);
        disposeObject(winnerBall); // Properly dispose textures and materials
        balls = balls.filter(b => b !== winnerBall);
        winnerBall = null;

        // Nếu người dùng vừa click yêu cầu quay tiếp, bắt đầu quay sau khi di chuyển xong
        if (pendingSpin) {
            pendingSpin = false;
            isSpinning = true;
            rotationSpeed.x = (Math.random() - 0.5) * 1.2;
            rotationSpeed.y = (Math.random() - 0.5) * 1.0;
            setTimeout(() => {
                pickWinner();
            }, 3000);
        }
    }
}

/**
 * Xử lý để tất cả quả cầu luôn hướng về camera
 */
function handleBallFacingCamera() {
    // Làm tất cả quả cầu hướng về camera (trừ quả cầu đang rơi)
    balls.forEach(ball => {
        if (!ball.userData.isFalling) {
            ball.lookAt(camera.position); // Hướng về camera
        }
    });

    // Làm quả cầu trúng thưởng hướng về camera và không xoay theo sphere
    if (winnerBall && winnerBall.userData.isFalling) {
        winnerBall.lookAt(camera.position);
    }
}

function saveStateToLocalStorage() {
    localStorage.setItem('drawn_numbers', JSON.stringify(drawnNumbers));
}

function clearStateInLocalStorage() {
    localStorage.removeItem('drawn_numbers');
    location.reload();
}

function loadStateFromLocalStorage() {
    try {
        const rawDrawnNumbers = localStorage.getItem('drawn_numbers');
        const restored = rawDrawnNumbers ? JSON.parse(rawDrawnNumbers) : [];
        if (!Array.isArray(drawnNumbers)) return;

        // Lọc hợp lệ
        drawnNumbers = restored.filter(n => Number.isInteger(n) && n >= 1 && n <= numberOfBalls);
        // Render vào grid và loại bỏ các bóng đã quay khỏi balls
        const drawnSet = new Set(drawnNumbers.map(Number));
        for (const n of drawnSet) {
            appendWinnerToList(n);
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
}

function onWindowResize() {
    updateRendererSize();
}

//Di chuyển quả cầu đến một ô trong danh sách ball
function moveWinnerBallToCell(targetCell) {
    // Nếu chưa có quả bóng winner thì thoát
    // Ngăn việc di chuyển nhiều lần cùng lúc
    if (!winnerBall || !targetCell || winnerBallMoving) return;

    // Đánh dấu là bóng đang di chuyển
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
    winnerBall.userData.moveSpeed = 0.04;
    winnerBall.userData.moveProgress = 0;
}

// Periodic cleanup to prevent memory leaks
function performPeriodicCleanup() {
    // Force garbage collection if available
    if (window.gc) {
        window.gc();
    }

    // Log memory usage
    if (performance.memory) {
        console.log('Memory usage:', {
            used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024) + 'MB',
            total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024) + 'MB',
            limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024) + 'MB',
            textureCache: canvasTextureCache.size + ' textures',
            activeBalls: balls.length + ' balls',
        });
    }
}
