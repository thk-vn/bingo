<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quay Số</title>
    <style>
        :root {
            --bg: #0f1724;
            --card: #071126;
            --accent: #ffb020;
            --accent-2: #22c1c3;
            --muted: #94a3b8;
            --win: #10b981;
            --font-color: #222222;
            --neon-color: #00f7ff;
            --neon-accent: #32ffe0;
        }

        body {
            margin: 0;
            overflow: hidden;
            background: linear-gradient(180deg, #071330 0%, #0b1a2d 100%);
            font-family: 'Inter', 'Segoe UI', 'Roboto', system-ui, Arial;
            color: #e6eef8;
        }

        /* GPU acceleration for better performance */
        #container, #drawnNumbers, button {
            will-change: transform;
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        #container {
            width: 66.67vw;
            height: 100vh;
            position: absolute;
            left: 0;
            top: 0;
        }

        #spinControls {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: calc(33.33vw - 40px);
            background: linear-gradient(180deg, #16324a, #0f3a68);
            padding: 15px 30px;
            border-radius: 15px;
            box-shadow: 0 6px 14px rgba(2, 6, 23, 0.6);
            text-align: center;
            z-index: 10;
            border: 2px solid var(--neon-color);
            box-sizing: border-box;
        }

        button {
            background: linear-gradient(180deg, #16324a, #0f3a68);
            color: var(--neon-color);
            border: 2px solid var(--neon-color);
            padding: 12px 30px;
            margin: 5px 0;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
            text-shadow: 0 0 6px var(--neon-color), 0 0 20px var(--neon-accent);
            box-shadow: 0 0 6px var(--neon-color), 0 0 18px var(--neon-accent), inset 0 0 6px rgba(255,255,255,0.1);
            width: 100%;
            box-sizing: border-box;
        }

        button:hover {
            transform: scale(1.05);
            color: var(--neon-accent);
            box-shadow: 0 0 12px var(--neon-color), 0 0 30px var(--neon-accent), inset 0 0 10px rgba(255,255,255,0.2);
            text-shadow: 0 0 10px var(--neon-accent), 0 0 30px var(--neon-accent);
        }

        button:active {
            transform: scale(0.95);
            box-shadow: 0 0 8px var(--neon-accent), 0 0 20px var(--neon-color);
        }

        #result {
            position: absolute;
            bottom: 30px;
            left: 30%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 40px;
            border-radius: 15px;
            font-size: 32px;
            font-weight: bold;
            color: #333;
            box-shadow: 0 4px 20px rgba(255, 255, 255, 0.5);
            display: none;
            z-index: 10;
        }


        #drawnNumbers {
            position: absolute;
            top: 0;
            right: 0;
            width: 33.33vw;
            height: 100vh;
            background: radial-gradient(circle at center, #1b1b2f, #0f0f1a);
            padding: 20px;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.15);
            overflow-y: auto;
            z-index: 10;
            border: 2px solid var(--neon-color);
            box-sizing: border-box;
        }

        #drawnNumbers h2 {
            margin: 0 0 15px 0;
            color: var(--neon-color);
            font-size: 20px;
            text-align: center;
            font-weight: 700;
        }

        .numbers-grid {
            display: grid;
            grid-template-columns: repeat(6, 64px);
            gap: 12px 12px;
            margin-top: 10px;
            justify-content: start;
            align-content: start;
        }

        .mini-ball {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 30% 30%, #fff3c4 0%, #f3d991 30%, #e4c47f 60%, #c9a75e 100%);
            box-shadow: inset -6px -10px 14px rgba(0,0,0,0.2), inset 8px 10px 18px rgba(255,255,255,0.35), 0 6px 14px rgba(0,0,0,0.35);
            border: 1px solid rgba(0,0,0,0.15);
            position: relative;
        }

        .mini-ball::after {
            content: '';
            position: absolute;
            top: 10%;
            left: 18%;
            width: 40%;
            height: 28%;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.9), rgba(255,255,255,0) 60%);
            pointer-events: none;
        }

        .mini-ball span {
            font-weight: 900;
            font-size: 22px;
            color: #da3b42;
            text-shadow: 0 1px 0 rgba(255,255,255,0.35), 0 0 4px rgba(0,0,0,0.25);
        }



        /* Responsive Design */
        @media (max-width: 1200px) {
            #container {
                width: 70vw;
            }

            #drawnNumbers {
                width: 30vw;
                height: 100vh;
            }

            #spinControls {
                width: calc(30vw - 40px);
            }

            .numbers-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

    </style>
</head>
<body>
<div id="container"></div>
<div id="result"></div>
<div id="drawnNumbers">
    <h2>Dãy số đã quay</h2>
    <div class="numbers-grid" id="numbersGrid"></div>
</div>
<div id="spinControls">
    <button onclick="resetSphere()">🔄 RESET</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
    let scene, camera, renderer, sphereGroup, ballsGroup; // Các đối tượng Three.js chính
    let balls = []; // Mảng chứa tất cả quả cầu số
    let rotationSpeed = { x: 0, y: 0 }; // Tốc độ quay của sphere và balls
    let isSpinning = false; // Trạng thái đang quay hay không
    let winnerBall = null; // Quả cầu trúng thưởng hiện tại
    let drawnNumbers = []; // Mảng lưu các số đã quay
    let numbersGrid = []; // Mảng lưu các ô số trong grid
    let winnerFloatingElement = null; // Element hiển thị số trúng thưởng
    let winnerBallMoving = false; // Trạng thái quả cầu đang di chuyển đến grid
    let winnerBallTarget = null; // Vị trí đích của quả cầu trúng thưởng
    let numberOfBalls = 50
    let pendingSpin = false; // Đánh dấu người dùng đã click để quay tiếp sau khi di chuyển winner
    let fireworks = []; // Các hệ hạt pháo hoa đang hoạt động
    const MAX_FIREWORKS = 3; // Giới hạn số lượng pháo hoa tối đa

    // Logo texture (gắn lên mỗi quả bóng)
    const logoUrl = "{{ Vite::asset('resources/images/thk_logo.png') }}";
    const textureLoader = new THREE.TextureLoader();
    const logoTexture = textureLoader.load(logoUrl);

    function init() {
        // Tạo scene 3D với nền đen
        scene = new THREE.Scene();
        scene.background = new THREE.Color(0x000000);

        // Tạo camera phối cảnh với góc nhìn 60 độ
        camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 14; // Đặt camera ở vị trí z, tăng hình cầu nhỏ, giảm thì to

        // Tạo renderer WebGL với khử răng cưa
        renderer = new THREE.WebGLRenderer({ antialias: true });
        updateRendererSize(); // Cập nhật kích thước renderer
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

        createDotSphere(); // Tạo khung lưới sphere
        createBalls(); // Tạo các quả cầu số
        createNumbersGrid(); // Tạo lưới hiển thị số đã quay

        // ===== XỬ LÝ THAY ĐỔI KÍCH THƯỚC CỬA SỔ =====
        window.addEventListener('resize', onWindowResize);

        // Bắt đầu vòng lặp animation
        animate();
    }

    // ===== TẠO KHUNG LƯỚI SPHERE =====
    function createDotSphere() {
        const radius = 6; // Bán kính của sphere
        const circlePoints = 128; // Số điểm để tạo đường tròn mượt mà

        // Tạo vật liệu cho các đường viền - màu trắng với độ trong suốt
        const circleMaterial = new THREE.LineBasicMaterial({
            color: 0xffffff, // Màu trắng
            linewidth: 3, // Độ dày đường viền
            transparent: true, // Cho phép trong suốt
            opacity: 0.8 // Độ trong suốt 80%
        });

        // ===== TẠO CÁC VÒNG NGANG (LATITUDE CIRCLES) =====
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

        // ===== TẠO CÁC VÒNG DỌC (LONGITUDE CIRCLES) =====
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

    function appendWinnerToList(number) {
        const grid = document.getElementById('numbersGrid');
        const ball = document.createElement('div');
        ball.className = 'mini-ball';
        const label = document.createElement('span');
        label.textContent = number;
        ball.appendChild(label);

        ball.id = `winner-${numbersGrid.length + 1}`;
        numbersGrid.push(ball);
        grid.appendChild(ball);
        return ball;
    }

    // ===== TẠO CÁC QUẢ CẦU SỐ =====
    function createBalls() {
        for (let i = 1; i <= numberOfBalls; i++) {
            const ballGroup = new THREE.Group(); // Tạo nhóm chứa quả cầu và chữ số

            // ===== TẠO QUẢ CẦU CHÍNH =====
            const sphere = new THREE.Mesh(
                new THREE.SphereGeometry(0.6, 32, 32), // Hình cầu bán kính 0.6, 32x32 segments
                new THREE.MeshPhongMaterial({
                    color: 0xe4c47f, // Màu vàng nhạt
                    shininess: 100 // Độ bóng cao
                })
            );
            ballGroup.add(sphere); // Thêm quả cầu vào nhóm

            // ===== TẠO CHỮ SỐ TRÊN QUẢ CẦU =====
            // Tạo canvas 2D để vẽ chữ số
            const canvas = document.createElement('canvas');
            canvas.width = 256; // Kích thước canvas
            canvas.height = 256;
            const ctx = canvas.getContext('2d');

            // Thiết lập style cho chữ số
            ctx.font = 'bold 120px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.lineWidth = 5;               // độ dày viền
            ctx.strokeStyle = '#ffffff';     // màu viền trắng
            ctx.fillStyle = '#da3b42';       // màu đỏ cho chữ
            ctx.strokeText(i.toString(), 128, 128);
            ctx.fillText(i.toString(), 128, 128);



            // Chuyển canvas thành texture và áp dụng lên mặt phẳng
            const texture = new THREE.CanvasTexture(canvas);
            const textMaterial = new THREE.MeshBasicMaterial({
                map: texture,
                transparent: true // Cho phép trong suốt
            });
            const textPlane = new THREE.Mesh(new THREE.PlaneGeometry(1, 1), textMaterial);
            textPlane.position.z = 0.61; // Đặt sát bề mặt quả cầu
            textPlane.renderOrder = 1; // dưới logo
            ballGroup.add(textPlane); // Thêm mặt phẳng chữ số vào nhóm

            // ===== GẮN LOGO TRÊN QUẢ CẦU =====
            const logoMaterial = new THREE.MeshBasicMaterial({
                map: logoTexture,
                transparent: true, // dùng alpha
                depthTest: true,   // vẫn bị quả bóng che khi quay ra sau
                depthWrite: false
            });
            // Logo nổi phía trước con số, không dính vào bề mặt quả bóng
            const logoPlane = new THREE.Mesh(new THREE.PlaneGeometry(0.45, 0.5), logoMaterial);
            logoPlane.position.set(0, 0.3, 0.7); // đẩy ra trước số rõ ràng
            logoPlane.renderOrder = 2; // trên số
            ballGroup.add(logoPlane);

            // ===== ĐẶT VỊ TRÍ NGẪU NHIÊN =====
            const phi = Math.random() * Math.PI * 2; // Góc ngẫu nhiên 0-2π
            const theta = Math.random() * Math.PI; // Góc ngẫu nhiên 0-π
            const radius = Math.random() * 4.5 + 0.5; // Bán kính ngẫu nhiên 0.5-5.0

            // Chuyển đổi tọa độ cầu sang tọa độ Descartes
            ballGroup.position.set(
                radius * Math.sin(theta) * Math.cos(phi), // X
                radius * Math.sin(theta) * Math.sin(phi), // Y
                radius * Math.cos(theta) // Z
            );

            // ===== LƯU THÔNG TIN QUẢ CẦU =====
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


    // ===== HÀM BẮT ĐẦU QUAY SỐ =====
    function startSpin() {
        // Nếu đang di chuyển sang danh sách thì không xử lý
        if (winnerBallMoving) return;

        // Nếu có winner đang đứng giữa, click này sẽ đưa nó sang danh sách rồi mới quay
        if (winnerBall && winnerBall.userData.isFalling && !winnerBallMoving) {
            const cell = appendWinnerToList(winnerBall.userData.number);
            pendingSpin = true;
            moveWinnerBallToCell(cell);
            return;
        }

        // Ngăn chặn quay nhiều lần cùng lúc
        if (isSpinning) return;

        isSpinning = true; // Đánh dấu đang quay
        document.getElementById('result').style.display = 'none'; // Ẩn kết quả cũ

        // ===== RESET QUẢ CẦU TRÚNG THƯỞNG CŨ (không còn đứng giữa) =====
        if (winnerBall) {
            scene.remove(winnerBall);
            ballsGroup.add(winnerBall);
            winnerBall.position.copy(winnerBall.userData.initialPos);
            winnerBall.scale.set(1, 1, 1);
            winnerBall.userData.isFalling = false;
            winnerBall.userData.velocity.set(
                (Math.random() - 0.5) * 0.03,
                (Math.random() - 0.5) * 0.03,
                (Math.random() - 0.5) * 0.03
            );
            winnerBall = null;
        }

        startNewSpin(); // Bắt đầu quay mới
    }

    // ===== HÀM BẮT ĐẦU QUAY MỚI =====
    function startNewSpin() {
        // Thiết lập tốc độ quay ngẫu nhiên - tăng cường độ xáo trộn
        rotationSpeed.x = (Math.random() - 0.5) * 1.2; // Tốc độ X: -0.6 đến 0.6 (tăng từ 0.8)
        rotationSpeed.y = (Math.random() - 0.5) * 1.0; // Tốc độ Y: -0.5 đến 0.5 (tăng từ 0.8)

        // Dừng quay sau 3 giây và chọn quả cầu trúng thưởng
        setTimeout(() => {
            pickWinner(); // Gọi hàm chọn quả cầu trúng thưởng
        }, 3000);
    }

    // ===== HÀM CHỌN QUẢ CẦU TRÚNG THƯỞNG =====
    function pickWinner() {
        // Ngăn chặn chọn nhiều quả cầu trúng thưởng cùng lúc
        if (winnerBall) return;

        isSpinning = false; // Dừng quay

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

            // Add to drawn numbers và hiển thị vào danh sách bên phải
            drawnNumbers.push(winner.userData.number);
            // Không di chuyển ngay; chờ click tiếp theo để di chuyển sang danh sách
            // (winnerBall sẽ đứng giữa màn hình cho tới khi người dùng click quay tiếp)
        }
    }

    function resetSphere() {
        rotationSpeed.x = 0;
        rotationSpeed.y = 0;
        sphereGroup.rotation.set(0, 0, 0);
        ballsGroup.rotation.set(0, 0, 0);
        isSpinning = false;
        document.getElementById('result').style.display = 'none';
        // document.getElementById('info').style.display = 'block';

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

        // Clean up all fireworks
        cleanupAllFireworks();
    }

    function cleanupWinnerBalls() {
        // Remove any balls that are falling (potential multiple winners)
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

    function cleanupAllFireworks() {
        // Clean up all fireworks to free memory
        fireworks.forEach(firework => {
            if (firework && firework.parent) {
                scene.remove(firework);
                firework.geometry.dispose();
                firework.material.dispose();
            }
        });
        fireworks = [];
    }

    // ===== HÀM ANIMATION CHÍNH - CHẠY LIÊN TỤC =====
    function animate() {
        requestAnimationFrame(animate); // Lên lịch frame tiếp theo

        const now = performance.now();
        if (!animate.lastTime) animate.lastTime = now;
        const deltaSeconds = (now - animate.lastTime) / 1000;
        animate.lastTime = now;

        // ===== XỬ LÝ KHI ĐANG QUAY =====
        if (isSpinning) {
            updateSphereRotation(); // Quay khung lưới & nhóm quả cầu
            updateBallMotionWhileSpinning(); // Cập nhật chuyển động vật lý của các quả cầu
        }

        // ===== XỬ LÝ KHI KHÔNG QUAY VÀ KHÔNG CÓ QUẢ CẦU TRÚNG THƯỞNG =====
        else if (!winnerBall) {
            idleMotion(); // Hiệu ứng quay nhẹ khi chờ
        }

        // ===== XỬ LÝ ANIMATION QUẢ CẦU TRÚNG THƯỞNG =====
        if (winnerBall && winnerBall.userData.isFalling) {
            if (!winnerBallMoving) {
                animateWinnerFalling(); // quả cầu rơi về giữa
            } else {
                animateWinnerMoveToGrid(); // di chuyển đến grid
            }
            // Trong khi winner đang di chuyển, sphere vẫn quay nhẹ
            sphereGroup.rotation.y += 0.01;
            ballsGroup.rotation.y += 0.01;
        }

        updateBallFacingCamera(); // Giúp các quả cầu luôn hướng về camera
        updateFireworks(deltaSeconds); // Cập nhật pháo hoa
        renderer.render(scene, camera); // Vẽ scene lên màn hình
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
        balls.forEach(ball => {
            if (!ball.userData.isFalling) { // Chỉ xử lý quả cầu không đang rơi
                ball.position.add(ball.userData.velocity); // Cập nhật vị trí theo vận tốc

                // Kiểm tra va chạm với biên sphere
                const distance = ball.position.length();
                if (distance > 5.2) { // Nếu vượt quá bán kính cho phép
                    const normal = ball.position.clone().normalize(); // Vector pháp tuyến
                    ball.userData.velocity.reflect(normal); // Phản xạ vận tốc
                    ball.userData.velocity.multiplyScalar(0.95); // Giảm vận tốc 5%
                    ball.position.setLength(5.2); // Đặt lại vị trí về biên
                }

                // Thêm chuyển động hỗn loạn ngẫu nhiên (60% khả năng) - tăng tần suất
                if (Math.random() < 0.8) {
                    ball.userData.velocity.x += (Math.random() - 0.5) * 0.05; // Tăng cường độ
                    ball.userData.velocity.y += (Math.random() - 0.5) * 0.05;
                    ball.userData.velocity.z += (Math.random() - 0.5) * 0.05;
                }

                // Thêm xoay ngẫu nhiên cho từng quả cầu
                ball.rotation.x += (Math.random() - 0.5) * 0.1;
                ball.rotation.y += (Math.random() - 0.5) * 0.1;
                ball.rotation.z += (Math.random() - 0.5) * 0.1;

                // Giới hạn vận tốc tối đa - tăng để xáo trộn nhiều hơn
                const speed = ball.userData.velocity.length();
                if (speed > 0.3) { // Tăng từ 0.2 lên 0.3
                    ball.userData.velocity.setLength(0.3);
                }
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
        winnerBall.position.add(winnerBall.userData.fallVelocity); // Cập nhật vị trí

        // Di chuyển về giữa màn hình (tọa độ 0,0,6) gần camera
        const targetPos = new THREE.Vector3(0, 0, 6);
        const direction = targetPos.clone().sub(winnerBall.position).normalize();
        winnerBall.userData.fallVelocity = direction.multiplyScalar(0.15); // Tốc độ rơi

        // Phóng to quả cầu dần (tối đa 3 lần)
        const scale = Math.min(winnerBall.scale.x + 0.04, 3);
        winnerBall.scale.set(scale, scale, scale);

        // Giữ quả cầu hướng về camera (không xoay)
        winnerBall.rotation.set(0, 0, 0);

        // Kiểm tra đã đến giữa màn hình chưa
        if (winnerBall.position.distanceTo(targetPos) < 1) {
            winnerBall.userData.fallVelocity.set(0, 0, 0); // Dừng rơi
            if (winnerBall.userData.centerScale === undefined) {
                winnerBall.userData.centerScale = winnerBall.scale.x;
                winnerBall.userData.pulsePhase = 0;
            }
            // Pháo hoa nổ quanh winner khi vừa đến giữa
            if (!winnerBall.userData.firedFireworks) {
                winnerBall.userData.firedFireworks = true;
                spawnFireworksAroundWinner();
            }
        }
    }

    // Quả cầu trúng thưởng di chuyển đến vị trí grid
    function animateWinnerMoveToGrid() {
        if (!winnerBallTarget) return;

        winnerBall.userData.moveProgress += winnerBall.userData.moveSpeed; // Tăng tiến độ

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

            // Xóa quả cầu khỏi scene
            scene.remove(winnerBall);
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

    // Giúp tất cả quả cầu luôn hướng về camera
    function updateBallFacingCamera() {
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

    //Di chuyển quả cầu đến một ô trong danh sách winner (bên phải)
    function moveWinnerBallToCell(targetCell) {
        if (!winnerBall) return; // Nếu chưa có quả bóng winner thì thoát
        if (!targetCell) return;

        // Ngăn việc di chuyển nhiều lần cùng lúc
        if (winnerBallMoving) return;

        winnerBallMoving = true; // Đánh dấu là bóng đang di chuyển

        // Lấy vị trí của ô đích và container trên màn hình
        const targetRect = targetCell.getBoundingClientRect();
        const containerRect = document.getElementById('container').getBoundingClientRect();

        // Chuyển toạ độ màn hình sang toạ độ 3D (World Coordinates)
        const x = ((targetRect.left + targetRect.width/2 - containerRect.left) / containerRect.width) * 2 - 1;
        const y = -((targetRect.top + targetRect.height/2 - containerRect.top) / containerRect.height) * 2 + 1;

        // Tạo vị trí mục tiêu trong không gian 3D
        winnerBallTarget = new THREE.Vector3(x * 8, y * 6, 5);

        // Cài đặt thông số di chuyển cho bóng (tốc độ nhanh hơn)
        winnerBall.userData.moveSpeed = 0.04;
        winnerBall.userData.moveProgress = 0;
    }

    function spawnFireworksAroundWinner() {
        if (!winnerBall) return;

        // Giới hạn số lượng pháo hoa - xóa cũ nếu quá nhiều
        while (fireworks.length >= MAX_FIREWORKS) {
            const oldFirework = fireworks.shift();
            if (oldFirework && oldFirework.parent) {
                scene.remove(oldFirework);
                oldFirework.geometry.dispose();
                oldFirework.material.dispose();
            }
        }

        const worldPos = new THREE.Vector3();
        winnerBall.getWorldPosition(worldPos);
        // Spawn few bursts around the winner (slightly more particles)
        spawnFireworks(worldPos, 0xffdd66, 300);
        spawnFireworks(worldPos.clone().add(new THREE.Vector3(0.3, 0.2, 0)), 0x66eeff, 200);
        spawnFireworks(worldPos.clone().add(new THREE.Vector3(-0.25, -0.15, 0)), 0xff66cc, 100);
    }

    function spawnFireworks(origin, colorHex = 0xffcc33, particleCount = 60) {
        const geometry = new THREE.BufferGeometry();
        const positions = new Float32Array(particleCount * 3);
        const velocities = new Float32Array(particleCount * 3);
        const lifetimes = new Float32Array(particleCount);

        for (let i = 0; i < particleCount; i++) {
            positions[i * 3 + 0] = origin.x;
            positions[i * 3 + 1] = origin.y;
            positions[i * 3 + 2] = origin.z;

            // random spherical direction
            const theta = Math.acos(2 * Math.random() - 1);
            const phi = 2 * Math.PI * Math.random();
            const speed = 0.05 + Math.random() * 0.12;
            velocities[i * 3 + 0] = Math.sin(theta) * Math.cos(phi) * speed;
            velocities[i * 3 + 1] = Math.cos(theta) * speed;
            velocities[i * 3 + 2] = Math.sin(theta) * Math.sin(phi) * speed;

            lifetimes[i] = 0.9 + Math.random() * 0.5; // seconds
        }

        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        geometry.setAttribute('velocity', new THREE.BufferAttribute(velocities, 3));
        geometry.setAttribute('lifetime', new THREE.BufferAttribute(lifetimes, 1));

        const material = new THREE.PointsMaterial({
            color: colorHex,
            size: 0.07,
            transparent: true,
            opacity: 1,
            depthWrite: false
        });

        const points = new THREE.Points(geometry, material);
        points.userData = { age: 0 };
        scene.add(points);
        fireworks.push(points);
    }

    function updateFireworks(deltaSeconds) {
        const gravity = -0.25;
        for (let i = fireworks.length - 1; i >= 0; i--) {
            const sys = fireworks[i];
            const geom = sys.geometry;
            const positions = geom.attributes.position.array;
            const velocities = geom.attributes.velocity.array;
            const lifetimes = geom.attributes.lifetime.array;

            sys.userData.age += deltaSeconds;

            const dt = deltaSeconds;
            let maxOpacity = 0;
            for (let p = 0; p < lifetimes.length; p++) {
                const base = p * 3;
                velocities[base + 1] += gravity * dt; // gravity on Y
                positions[base + 0] += velocities[base + 0];
                positions[base + 1] += velocities[base + 1];
                positions[base + 2] += velocities[base + 2];
                lifetimes[p] -= dt;
                if (lifetimes[p] > maxOpacity) maxOpacity = lifetimes[p];
            }

            geom.attributes.position.needsUpdate = true;
            sys.material.opacity = Math.max(0, Math.min(1, maxOpacity));

            // cleanup
            const alive = lifetimes.some(v => v > 0);
            if (!alive || sys.userData.age > 2.2) {
                scene.remove(sys);
                fireworks.splice(i, 1);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const containerEl = document.getElementById('container');
        if (containerEl) {
            containerEl.addEventListener('click', () => {
                if (!isSpinning && !winnerBallMoving) {
                    startSpin();
                }
            });
        }
    });

    init();
</script>
</body>
</html>
