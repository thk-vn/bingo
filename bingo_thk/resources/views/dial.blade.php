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
        #container, #drawnNumbers, .number-cell, button {
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

        #info {
            position: absolute;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 14px;
            text-align: center;
            opacity: 0.7;
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
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-top: 10px;
        }

        .number-cell {
            width: 100%;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, rgba(0, 255, 255, 0.08) 0%, rgba(0, 200, 255, 0.04) 50%, rgba(255, 255, 255, 0.02) 100%);
            border: 2px solid var(--neon-accent);
            border-radius: 10px;
            color: var(--neon-color);
            font-weight: bold;
            font-size: 20px;
            transition: all 0.3s;
            cursor: pointer;
            box-sizing: border-box;
            box-shadow: inset 0 0 6px rgba(255, 255, 255, 0.02);
        }

        .number-cell:hover {
            transform: scale(1.08);
            background: linear-gradient(145deg, rgba(0, 255, 255, 0.25) 0%, rgba(0, 200, 255, 0.15) 50%, rgba(255, 255, 255, 0.05) 100%);
            box-shadow: 0 0 8px var(--neon-color), 0 0 15px var(--neon-accent);
        }

        @keyframes textPulse {
            0% {
                text-shadow: 0 0 4px var(--neon-color), 0 0 10px var(--neon-accent);
            }
            100% {
                text-shadow: 0 0 8px var(--neon-accent), 0 0 20px var(--neon-color);
            }
        }

        .number-cell.drawn {
            background: linear-gradient(180deg, var(--accent), #ffa44a);
            border: 2px solid var(--accent);
            box-shadow: 0 0 8px rgba(255, 176, 32, 0.6), 0 0 20px rgba(255, 176, 32, 0.4), inset 0 0 8px rgba(255, 255, 255, 0.2);
            color: #071126;
            transform: scale(1.05);
        }

        .number-cell.drawn.active {
            background: var(--neon-accent);
            border: 2px solid var(--neon-accent);
            box-shadow: 0 0 25px var(--neon-accent), 0 0 50px var(--neon-color);
            color: #fff;
            transform: scale(1.08);
            /* Animation only when active, not continuous */
        }

        @keyframes pulseGlow {
            0% {
                text-shadow: 0 0 5px var(--neon-color), 0 0 10px var(--neon-accent);
                box-shadow: 0 0 5px var(--neon-color), 0 0 15px var(--neon-accent), inset 0 0 5px rgba(255,255,255,0.1);
            }
            100% {
                text-shadow: 0 0 10px var(--neon-accent), 0 0 25px var(--neon-accent);
                box-shadow: 0 0 10px var(--neon-color), 0 0 30px var(--neon-accent), inset 0 0 10px rgba(255,255,255,0.2);
            }
        }

        @keyframes neonSweep {
            0% {
                box-shadow: 0 0 6px var(--neon-accent), inset 0 0 8px rgba(255,255,255,0.1);
                background: linear-gradient(90deg, rgba(255,255,255,0.1) 0%, var(--neon-color) 50%, rgba(255,255,255,0.1) 100%);
                background-size: 200% 100%;
                background-position: -100% 0;
            }
            100% {
                background-position: 100% 0;
                box-shadow: 0 0 15px var(--neon-color), 0 0 40px var(--neon-accent), inset 0 0 10px rgba(255,255,255,0.2);
            }
        }

        #winnerDisplay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: radial-gradient(circle at center, #1b1b2f, #0f0f1a);
            padding: 30px 50px;
            border-radius: 20px;
            font-size: 36px;
            font-weight: bold;
            color: var(--neon-color);
            box-shadow: 0 0 25px rgba(0, 255, 255, 0.25), 0 0 60px rgba(255, 0, 255, 0.15);
            display: none;
            z-index: 20;
            text-align: center;
            border: 3px solid var(--neon-color);
            text-shadow: 0 0 6px var(--neon-color), 0 0 16px var(--neon-accent);
        }

        #winnerDisplay .winner-number {
            font-size: 48px;
            color: var(--neon-accent);
            margin: 10px 0;
            text-shadow: 0 0 10px var(--neon-color), 0 0 25px var(--neon-accent), 0 0 50px var(--neon-accent);
        }

        @keyframes bingoGlow {
            0% {
                text-shadow: 0 0 6px var(--neon-color), 0 0 20px var(--neon-accent), 0 0 40px var(--neon-color);
                transform: scale(1);
            }
            100% {
                text-shadow: 0 0 12px var(--neon-accent), 0 0 40px var(--neon-color), 0 0 80px var(--neon-accent);
                transform: scale(1.1);
            }
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
    <button onclick="startSpin()">🎲 QUAY SỐ</button>
    <button onclick="resetSphere()">🔄 RESET</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
    let scene, camera, renderer, sphereGroup, ballsGroup; // Các đối tượng Three.js chính
    let balls = []; // Mảng chứa tất cả quả cầu số
    let rotationSpeed = { x: 0, y: 0 }; // Tốc độ quay của sphere và balls
    let isSpinning = false; // Trạng thái đang quay hay không
    let winnerBall = null; // Quả cầu trúng thưởng hiện tại
    let mouse = { x: 0, y: 0 }; // Vị trí chuột để điều khiển
    let mouseDown = false; // Trạng thái nhấn chuột
    let drawnNumbers = []; // Mảng lưu các số đã quay
    let numbersGrid = []; // Mảng lưu các ô số trong grid
    let winnerFloatingElement = null; // Element hiển thị số trúng thưởng
    let winnerBallMoving = false; // Trạng thái quả cầu đang di chuyển đến grid
    let winnerBallTarget = null; // Vị trí đích của quả cầu trúng thưởng
    let numberOfBalls = 50

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

        // ===== THIẾT LẬP ĐIỀU KHIỂN CHUỘT =====
        document.addEventListener('mousedown', onMouseDown); // Nhấn chuột
        document.addEventListener('mousemove', onMouseMove); // Di chuyển chuột
        document.addEventListener('mouseup', onMouseUp); // Thả chuột

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

        for (let i = 1; i <= numberOfBalls; i++) {
            const cell = document.createElement('div');
            cell.className = 'number-cell';
            cell.textContent = i;
            cell.id = `number-${i}`;
            numbersGrid[i] = cell;
            grid.appendChild(cell);
        }
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
            ctx.fillStyle = '#da3b42'; // Màu đỏ
            ctx.font = 'bold 120px Arial'; // Font chữ to, đậm
            ctx.textAlign = 'center'; // Căn giữa ngang
            ctx.textBaseline = 'middle'; // Căn giữa dọc
            ctx.fillText(i.toString(), 128, 128); // Vẽ số ở giữa canvas

            // Chuyển canvas thành texture và áp dụng lên mặt phẳng
            const texture = new THREE.CanvasTexture(canvas);
            const textMaterial = new THREE.MeshBasicMaterial({
                map: texture,
                transparent: true // Cho phép trong suốt
            });
            const textPlane = new THREE.Mesh(new THREE.PlaneGeometry(1, 1), textMaterial);
            textPlane.position.z = 0.61; // Đặt sát bề mặt quả cầu
            ballGroup.add(textPlane); // Thêm mặt phẳng chữ số vào nhóm

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
        // Ngăn chặn quay nhiều lần cùng lúc
        if (isSpinning) return;

        // Ngăn chặn quay khi quả cầu trúng thưởng đang di chuyển đến grid
        if (winnerBallMoving) return;

        // Nếu có quả cầu trúng thưởng ở giữa, di chuyển nó đến grid trước khi quay mới
        if (winnerBall && winnerBall.userData.isFalling && !winnerBallMoving) {
            moveWinnerBallToGrid(winnerBall.userData.number); // Di chuyển quả cầu đến grid
            return;
        }

        isSpinning = true; // Đánh dấu đang quay
        document.getElementById('result').style.display = 'none'; // Ẩn kết quả cũ

        // ===== RESET QUẢ CẦU TRÚNG THƯỞNG CŨ =====
        if (winnerBall) {
            scene.remove(winnerBall); // Xóa khỏi scene
            ballsGroup.add(winnerBall); // Thêm lại vào nhóm balls
            winnerBall.position.copy(winnerBall.userData.initialPos); // Về vị trí ban đầu
            winnerBall.scale.set(1, 1, 1); // Reset kích thước
            winnerBall.userData.isFalling = false; // Đánh dấu không rơi
            winnerBall.userData.velocity.set( // Reset vận tốc ngẫu nhiên
                (Math.random() - 0.5) * 0.03,
                (Math.random() - 0.5) * 0.03,
                (Math.random() - 0.5) * 0.03
            );
            winnerBall = null; // Xóa tham chiếu
        }

        startNewSpin(); // Bắt đầu quay mới
    }

    // ===== HÀM BẮT ĐẦU QUAY MỚI =====
    function startNewSpin() {
        // Thiết lập tốc độ quay ngẫu nhiên - tăng cường độ xáo trộn
        rotationSpeed.x = (Math.random() - 0.5) * 1.2; // Tốc độ X: -0.6 đến 0.6 (tăng từ 0.8)
        rotationSpeed.y = (Math.random() - 0.5) * 1.0; // Tốc độ Y: -0.5 đến 0.5 (tăng từ 0.8)

        // Dừng quay sau 4 giây và chọn quả cầu trúng thưởng
        setTimeout(() => {
            pickWinner(); // Gọi hàm chọn quả cầu trúng thưởng
        }, 4000);
    }

    // ===== HÀM CHỌN QUẢ CẦU TRÚNG THƯỞNG =====
    function pickWinner() {
        // Ngăn chặn chọn nhiều quả cầu trúng thưởng cùng lúc
        if (winnerBall) return;

        isSpinning = false; // Dừng quay

        // ===== TÌM QUẢ CẦU GẦN CAMERA NHẤT =====
        // Tìm quả cầu có tọa độ Z cao nhất (gần camera nhất)
        let maxZ = -Infinity; // Giá trị Z cao nhất
        let winner = null; // Quả cầu trúng thưởng

        balls.forEach(ball => {
            if (!ball.userData.isFalling) { // Chỉ xét quả cầu không đang rơi
                const worldPos = new THREE.Vector3();
                ball.getWorldPosition(worldPos); // Lấy vị trí thế giới thực
                if (worldPos.z > maxZ) { // Nếu Z lớn hơn giá trị hiện tại
                    maxZ = worldPos.z; // Cập nhật Z cao nhất
                    winner = ball; // Lưu quả cầu này
                }
            }
        });

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

            // Add to drawn numbers
            drawnNumbers.push(winner.userData.number);

            // Winner ball stays in center, waiting for next spin

            // const result = document.getElementById('result');
            // result.textContent = `🎉 SỐ TRÚNG THƯỞNG: ${ winner.userData.number }`;
            // result.style.display = 'block';
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

    // ===== HÀM ANIMATION CHÍNH - CHẠY LIÊN TỤC =====
    function animate() {
        requestAnimationFrame(animate); // Lên lịch frame tiếp theo

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
        }

        updateBallFacingCamera(); // Giúp các quả cầu luôn hướng về camera
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
                if (Math.random() < 0.6) {
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
        const scale = Math.min(winnerBall.scale.x + 0.025, 4);
        winnerBall.scale.set(scale, scale, scale);

        // Giữ quả cầu hướng về camera (không xoay)
        winnerBall.rotation.set(0, 0, 0);

        // Kiểm tra đã đến giữa màn hình chưa
        if (winnerBall.position.distanceTo(targetPos) < 1) {
            winnerBall.userData.fallVelocity.set(0, 0, 0); // Dừng rơi
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
            updateNumbersGrid(number); // Cập nhật ô số trong grid

            // Xóa quả cầu khỏi scene
            scene.remove(winnerBall);
            balls = balls.filter(b => b !== winnerBall);
            winnerBall = null;

            // Bắt đầu quay cho quả cầu mới
            isSpinning = true;
            rotationSpeed.x = (Math.random() - 0.5) * 0.8;
            rotationSpeed.y = (Math.random() - 0.5) * 0.8;

            // Chọn quả cầu trúng thưởng mới sau 3 giây
            setTimeout(() => {
                pickWinner();
            }, 3000);
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


    // ===== ĐIỀU KHIỂN CHUỘT =====
    function onMouseDown(e) {
        if (isSpinning) return; // Không cho điều khiển khi đang quay
        mouseDown = true; // Đánh dấu đang nhấn chuột
        mouse.x = e.clientX; // Lưu vị trí X của chuột
        mouse.y = e.clientY; // Lưu vị trí Y của chuột
    }

    function onMouseMove(e) {
        if (!mouseDown || isSpinning) return; // Chỉ xử lý khi đang nhấn chuột và không quay

        // Tính toán khoảng cách di chuyển chuột
        const deltaX = e.clientX - mouse.x;
        const deltaY = e.clientY - mouse.y;

        // Quay sphere theo di chuyển chuột
        sphereGroup.rotation.y += deltaX * 0.005; // Quay trục Y theo di chuyển ngang
        sphereGroup.rotation.x += deltaY * 0.005; // Quay trục X theo di chuyển dọc

        // Quay nhóm balls cùng với sphere
        ballsGroup.rotation.y += deltaX * 0.005;
        ballsGroup.rotation.x += deltaY * 0.005;

        // Cập nhật vị trí chuột
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    }

    function onMouseUp() {
        mouseDown = false; // Đánh dấu thả chuột
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

    //Di chuyển quả cầu đến grid
    function moveWinnerBallToGrid(number) {
        if (!winnerBall) return; // Nếu chưa có quả bóng winner thì thoát

        // Ngăn việc di chuyển nhiều lần cùng lúc
        if (winnerBallMoving) return;

        winnerBallMoving = true; // Đánh dấu là bóng đang di chuyển

        // Lấy ô đích (nơi chứa số tương ứng trong grid)
        const targetCell = numbersGrid[number];
        if (!targetCell) return; // Nếu không tìm thấy ô thì thoát

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

    // ===== CẬP NHẬT LƯỚI SỐ ĐÃ QUAY =====
    function updateNumbersGrid(number) {
        const cell = numbersGrid[number]; // Lấy ô số tương ứng
        if (cell) {
            cell.classList.add('drawn'); // Đánh dấu số đã quay

            // Xóa class active khỏi tất cả ô
            document.querySelectorAll('.number-cell').forEach(cell => {
                cell.classList.remove('active');
            });

            // Thêm class active cho số hiện tại
            cell.classList.add('active');

            // Xóa class active sau 2 giây
            setTimeout(() => {
                cell.classList.remove('active');
            }, 2000);
        }
    }

    init();
</script>
</body>
</html>
