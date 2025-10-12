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

        #controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(180deg, #16324a, #0f3a68);
            padding: 15px 30px;
            border-radius: 15px;
            box-shadow: 0 6px 14px rgba(2, 6, 23, 0.6);
            text-align: center;
            z-index: 10;
            border: 2px solid var(--neon-color);
        }

        button {
            background: linear-gradient(180deg, #16324a, #0f3a68);
            color: var(--neon-color);
            border: 2px solid var(--neon-color);
            padding: 12px 30px;
            margin: 5px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
            text-shadow: 0 0 6px var(--neon-color), 0 0 20px var(--neon-accent);
            box-shadow: 0 0 6px var(--neon-color), 0 0 18px var(--neon-accent), inset 0 0 6px rgba(255,255,255,0.1);
            /* Removed continuous animation for performance */
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
            border-radius: 15px;
        }

        #drawnNumbers h2 {
            margin: 0 0 15px 0;
            color: var(--neon-color);
            font-size: 18px;
            text-align: center;
            text-shadow: 0 0 4px var(--neon-color);
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
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, rgba(0, 255, 255, 0.08) 0%, rgba(0, 200, 255, 0.04) 50%, rgba(255, 255, 255, 0.02) 100%);
            border: 2px solid var(--neon-accent);
            border-radius: 10px;
            color: var(--neon-color);
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s;
            cursor: pointer;
            box-sizing: border-box;
            text-shadow: 0 0 4px var(--neon-color);
            /* Reduced blur and shadow for performance */
            box-shadow: inset 0 0 6px rgba(255, 255, 255, 0.02);
            /* Removed continuous animation for performance */
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
            /* Removed continuous animation for performance */
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
            /* Removed continuous animation for performance */
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
            }
            
            .numbers-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        @media (max-width: 768px) {
            #container {
                width: 100vw;
                height: 60vh;
            }
            
            #drawnNumbers {
                width: 100vw;
                height: 40vh;
                top: 60vh;
                right: 0;
            }
            
            .numbers-grid {
                grid-template-columns: repeat(8, 1fr);
                gap: 4px;
            }
            
            .number-cell {
                height: 35px;
                font-size: 12px;
            }
            
            #controls {
                bottom: 10px;
                padding: 10px 20px;
            }
            
            button {
                padding: 8px 20px;
                font-size: 14px;
                margin: 3px;
            }
        }

        @media (max-width: 480px) {
            .numbers-grid {
                grid-template-columns: repeat(6, 1fr);
            }
            
            .number-cell {
                height: 30px;
                font-size: 10px;
            }
            
            #drawnNumbers h2 {
                font-size: 16px;
            }
            
        }
    </style>
</head>
<body>
<div id="container"></div>

{{--<div id="info"></div>--}}
<div id="result"></div>
{{--<div id="winnerDisplay">--}}
{{--    <div>🎉 SỐ TRÚNG THƯỞNG 🎉</div>--}}
{{--    <div class="winner-number" id="winnerNumber"></div>--}}
{{--</div>--}}

<div id="drawnNumbers">
    <h2>Dãy số đã quay</h2>
    <div class="numbers-grid" id="numbersGrid"></div>
</div>
<div id="controls">
    <button onclick="startSpin()">🎲 QUAY SỐ</button>
    <button onclick="resetSphere()">🔄 RESET</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
    let scene, camera, renderer, sphereGroup, ballsGroup;
    let balls = [];
    let rotationSpeed = { x: 0, y: 0 };
    let isSpinning = false;
    let winnerBall = null;
    let mouse = { x: 0, y: 0 };
    let mouseDown = false;
    let drawnNumbers = [];
    let numbersGrid = [];
    let winnerFloatingElement = null;
    let winnerBallMoving = false;
    let winnerBallTarget = null;

    function init() {
        // Scene
        scene = new THREE.Scene();
        scene.background = new THREE.Color(0x000000);

        // Camera
        camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 12;

        // Renderer
        renderer = new THREE.WebGLRenderer({ antialias: true });
        updateRendererSize();
        document.getElementById('container').appendChild(renderer.domElement);

        // Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
        scene.add(ambientLight);

        const pointLight1 = new THREE.PointLight(0xffffff, 1, 100);
        pointLight1.position.set(10, 10, 10);
        scene.add(pointLight1);

        const pointLight2 = new THREE.PointLight(0xffffff, 0.5, 100);
        pointLight2.position.set(-10, -10, 5);
        scene.add(pointLight2);

        // Create sphere group
        sphereGroup = new THREE.Group();
        scene.add(sphereGroup);

        // Create balls group (inside sphere)
        ballsGroup = new THREE.Group();
        scene.add(ballsGroup);

        // Create sphere with dots
        createDotSphere();

        // Create balls with numbers inside sphere
        createBalls();

        // Create numbers grid
        createNumbersGrid();

        // Mouse controls
        document.addEventListener('mousedown', onMouseDown);
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
        document.addEventListener('wheel', onWheel);

        // Touch controls
        document.addEventListener('touchstart', onTouchStart);
        document.addEventListener('touchmove', onTouchMove);

        // Handle window resize
        window.addEventListener('resize', onWindowResize);

        // Start animation
        animate();
    }

    // function createDotSphere() {
    //     const radius = 6;
    //     const numDots = 500;
    //     const goldenRatio = (1 + Math.sqrt(5)) / 2;
    //
    //     // Create dots on sphere surface using Fibonacci sphere
    //     const dotGeometry = new THREE.SphereGeometry(0.04, 8, 8);
    //     const dotMaterial = new THREE.MeshBasicMaterial({ color: 0xffffff });
    //
    //     for (let i = 0; i < numDots; i++) {
    //         const theta = 2 * Math.PI * i / goldenRatio;
    //         const phi = Math.acos(1 - 2 * (i + 0.5) / numDots);
    //
    //         const x = radius * Math.sin(phi) * Math.cos(theta);
    //         const y = radius * Math.sin(phi) * Math.sin(theta);
    //         const z = radius * Math.cos(phi);
    //
    //         const dot = new THREE.Mesh(dotGeometry, dotMaterial);
    //         dot.position.set(x, y, z);
    //         sphereGroup.add(dot);
    //     }
    //
    //     // Add wireframe circles for latitude/longitude effect
    //     const circlePoints = 64;
    //     const circleMaterial = new THREE.LineBasicMaterial({
    //         color: 0xffffff,
    //         transparent: true,
    //         opacity: 0.15
    //     });
    //
    //     // Latitude circles
    //     for (let lat = -Math.PI / 2; lat <= Math.PI / 2; lat += Math.PI / 6) {
    //         const points = [];
    //         const r = radius * Math.cos(lat);
    //         const y = radius * Math.sin(lat);
    //
    //         for (let i = 0; i <= circlePoints; i++) {
    //             const angle = (i / circlePoints) * Math.PI * 2;
    //             points.push(new THREE.Vector3(
    //                 r * Math.cos(angle),
    //                 y,
    //                 r * Math.sin(angle)
    //             ));
    //         }
    //
    //         const geometry = new THREE.BufferGeometry().setFromPoints(points);
    //         const circle = new THREE.Line(geometry, circleMaterial);
    //         sphereGroup.add(circle);
    //     }
    //
    //     // Longitude circles
    //     for (let i = 0; i < 12; i++) {
    //         const points = [];
    //         const angle = (i / 12) * Math.PI;
    //
    //         for (let j = 0; j <= circlePoints; j++) {
    //             const phi = (j / circlePoints) * Math.PI * 2;
    //             points.push(new THREE.Vector3(
    //                 radius * Math.sin(phi) * Math.cos(angle),
    //                 radius * Math.cos(phi),
    //                 radius * Math.sin(phi) * Math.sin(angle)
    //             ));
    //         }
    //
    //         const geometry = new THREE.BufferGeometry().setFromPoints(points);
    //         const circle = new THREE.Line(geometry, circleMaterial);
    //         sphereGroup.add(circle);
    //     }
    // }

    function createDotSphere() {
        const radius = 6;
        const circlePoints = 128;

        // Làm đường viền rõ hơn (màu sáng và đậm)
        const circleMaterial = new THREE.LineBasicMaterial({
            color: 0xffffff, // màu xanh ngọc nổi bật
            linewidth: 3,
            transparent: true,
            opacity: 0.8
        });

        // Latitude circles (vòng ngang)
        for (let lat = -Math.PI / 2; lat <= Math.PI / 2; lat += Math.PI / 8) {
            const points = [];
            const r = radius * Math.cos(lat);
            const y = radius * Math.sin(lat);

            for (let i = 0; i <= circlePoints; i++) {
                const angle = (i / circlePoints) * Math.PI * 2;
                points.push(new THREE.Vector3(
                    r * Math.cos(angle),
                    y,
                    r * Math.sin(angle)
                ));
            }

            const geometry = new THREE.BufferGeometry().setFromPoints(points);
            const circle = new THREE.Line(geometry, circleMaterial);
            sphereGroup.add(circle);
        }

        // Longitude circles (vòng dọc)
        for (let i = 0; i < 24; i++) {
            const points = [];
            const angle = (i / 24) * Math.PI;

            for (let j = 0; j <= circlePoints; j++) {
                const phi = (j / circlePoints) * Math.PI * 2;
                points.push(new THREE.Vector3(
                    radius * Math.sin(phi) * Math.cos(angle),
                    radius * Math.cos(phi),
                    radius * Math.sin(phi) * Math.sin(angle)
                ));
            }

            const geometry = new THREE.BufferGeometry().setFromPoints(points);
            const circle = new THREE.Line(geometry, circleMaterial);
            sphereGroup.add(circle);
        }
    }

    function createNumbersGrid() {
        const grid = document.getElementById('numbersGrid');
        grid.innerHTML = '';

        for (let i = 1; i <= 99; i++) {
            const cell = document.createElement('div');
            cell.className = 'number-cell';
            cell.textContent = i;
            cell.id = `number-${i}`;
            numbersGrid[i] = cell;
            grid.appendChild(cell);
        }
    }

    function createBalls() {
        for (let i = 1; i <= 99; i++) {
            const ballGroup = new THREE.Group();

            // Quả cầu
            const sphere = new THREE.Mesh(
                new THREE.SphereGeometry(0.6, 32, 32),
                new THREE.MeshPhongMaterial({ color: 0xe4c47f, shininess: 100 })
            );
            ballGroup.add(sphere);

            // Canvas chứa chữ
            const canvas = document.createElement('canvas');
            canvas.width = 256;
            canvas.height = 256;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#da3b42';
            ctx.font = 'bold 120px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(i.toString(), 128, 128);

            const texture = new THREE.CanvasTexture(canvas);
            const textMaterial = new THREE.MeshBasicMaterial({ map: texture, transparent: true });
            const textPlane = new THREE.Mesh(new THREE.PlaneGeometry(1, 1), textMaterial);
            textPlane.position.z = 0.61;// nằm sát bề mặt cầu
            ballGroup.add(textPlane);

            // Random vị trí
            const phi = Math.random() * Math.PI * 2;
            const theta = Math.random() * Math.PI;
            const radius = Math.random() * 4.5 + 0.5;
            ballGroup.position.set(
                radius * Math.sin(theta) * Math.cos(phi),
                radius * Math.sin(theta) * Math.sin(phi),
                radius * Math.cos(theta)
            );

            ballGroup.userData = {
                number: i,
                initialPos: ballGroup.position.clone(),
                velocity: new THREE.Vector3((Math.random() - 0.5) * 0.03, (Math.random() - 0.5) * 0.03, (Math.random() - 0.5) * 0.03),
                isFalling: false,
            };

            ballsGroup.add(ballGroup);
            balls.push(ballGroup);
        }
    }


    function startSpin() {
        // Prevent multiple spins
        if (isSpinning) return;
        
        // Prevent spin if winner ball is moving to grid
        if (winnerBallMoving) return;

        // If there's a winner ball in center, move it to grid first, then start spinning
        if (winnerBall && winnerBall.userData.isFalling && !winnerBallMoving) {
            // Move winner to grid first
            moveWinnerBallToGrid(winnerBall.userData.number);
            return;
        }

        isSpinning = true;
        document.getElementById('result').style.display = 'none';
        // document.getElementById('info').style.display = 'none';

        // Reset any previous winner
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

        startNewSpin();
    }

    function startNewSpin() {
        rotationSpeed.x = (Math.random() - 0.5) * 0.8;
        rotationSpeed.y = (Math.random() - 0.5) * 0.8;

        // Stop after 4 seconds and pick winner
        setTimeout(() => {
            pickWinner();
        }, 4000);
    }

    function pickWinner() {
        // Prevent multiple winners
        if (winnerBall) return;
        
        isSpinning = false;

        // Find ball closest to front (highest z in world space)
        let maxZ = -Infinity;
        let winner = null;

        balls.forEach(ball => {
            if (!ball.userData.isFalling) {
                const worldPos = new THREE.Vector3();
                ball.getWorldPosition(worldPos);
                if (worldPos.z > maxZ) {
                    maxZ = worldPos.z;
                    winner = ball;
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

    function animate() {
        requestAnimationFrame(animate);

        if (isSpinning) {
            sphereGroup.rotation.x += rotationSpeed.x;
            sphereGroup.rotation.y += rotationSpeed.y;

            ballsGroup.rotation.x += rotationSpeed.x;
            ballsGroup.rotation.y += rotationSpeed.y;

            // Slow down faster
            rotationSpeed.x *= 0.98;
            rotationSpeed.y *= 0.98;

            // Make balls bounce more during spin - optimized
            balls.forEach(ball => {
                if (!ball.userData.isFalling) {
                    ball.position.add(ball.userData.velocity);

                    const distance = ball.position.length();
                    if (distance > 5.2) {
                        const normal = ball.position.clone().normalize();
                        ball.userData.velocity.reflect(normal);
                        ball.userData.velocity.multiplyScalar(0.95);
                        ball.position.setLength(5.2);
                    }

                    // Add chaos during spin - reduced frequency
                    if (Math.random() < 0.3) {
                        ball.userData.velocity.x += (Math.random() - 0.5) * 0.03;
                        ball.userData.velocity.y += (Math.random() - 0.5) * 0.03;
                        ball.userData.velocity.z += (Math.random() - 0.5) * 0.03;
                    }

                    // Limit velocity
                    const speed = ball.userData.velocity.length();
                    if (speed > 0.2) {
                        ball.userData.velocity.setLength(0.2);
                    }
                }
            });
        } else if (!winnerBall) {
            // Gentle idle rotation - faster
            sphereGroup.rotation.y += 0.008;
            ballsGroup.rotation.y += 0.008;

            // Gentle ball movement - optimized
            balls.forEach(ball => {
                if (!ball.userData.isFalling) {
                    ball.position.add(ball.userData.velocity);

                    const distance = ball.position.length();
                    if (distance > 5.2) {
                        const normal = ball.position.clone().normalize();
                        ball.userData.velocity.reflect(normal);
                        ball.userData.velocity.multiplyScalar(0.85);
                        ball.position.setLength(5.2);
                    }
                }
            });
        }
        // When winner is selected, no rotation or movement at all

        // Animate falling winner - only if there's exactly one winner
        if (winnerBall && winnerBall.userData.isFalling) {
            if (!winnerBallMoving) {
                // Initial fall to center
                winnerBall.position.add(winnerBall.userData.fallVelocity);

                // Move toward center of screen - faster
                const targetPos = new THREE.Vector3(0, 0, 5);
                const direction = targetPos.clone().sub(winnerBall.position).normalize();
                winnerBall.userData.fallVelocity = direction.multiplyScalar(0.15);

                // Scale up the ball - faster
                const scale = Math.min(winnerBall.scale.x + 0.025, 3);
                winnerBall.scale.set(scale, scale, scale);

                // Keep winner ball facing camera - no rotation
                winnerBall.rotation.set(0, 0, 0);

                // Check if reached center - then stop and wait
                if (winnerBall.position.distanceTo(targetPos) < 1) {
                    winnerBall.userData.fallVelocity.set(0, 0, 0);
                    // Ball stays in center, waiting for next spin
                }
            } else {
                // Move to grid position
                if (winnerBallTarget) {
                    winnerBall.userData.moveProgress += winnerBall.userData.moveSpeed;
                    
                    if (winnerBall.userData.moveProgress < 1) {
                        // Smooth movement using easing
                        const t = winnerBall.userData.moveProgress;
                        const easedT = t * t * (3 - 2 * t); // Smooth step
                        
                        winnerBall.position.lerpVectors(
                            new THREE.Vector3(0, 0, 5), // Start position (center)
                            winnerBallTarget, // Target position
                            easedT
                        );
                        
                        // Scale down as it moves
                        const scale = 3 - (t * 2.5); // From 3 to 0.5
                        winnerBall.scale.set(scale, scale, scale);
                    } else {
                        // Movement complete
                        winnerBallMoving = false;
                        winnerBallTarget = null;
                        
                        // Update grid and remove ball
                        const number = winnerBall.userData.number;
                        updateNumbersGrid(number);
                        
                        // Remove ball from scene
                        scene.remove(winnerBall);
                        balls = balls.filter(b => b !== winnerBall);
                        winnerBall = null;
                        
                        // Now start spinning for new winner
                        isSpinning = true;
                        rotationSpeed.x = (Math.random() - 0.5) * 0.8;
                        rotationSpeed.y = (Math.random() - 0.5) * 0.8;
                        
                        // Pick new winner after spinning
                        setTimeout(() => {
                            pickWinner();
                        }, 3000);
                    }
                }
            }
        }

        // Make all balls face camera (except falling one)
        balls.forEach(ball => {
            if (!ball.userData.isFalling) {
                ball.lookAt(camera.position);
            }
        });

        // Make winner ball face camera and don't rotate with sphere
        if (winnerBall && winnerBall.userData.isFalling) {
            winnerBall.lookAt(camera.position);
        }

        renderer.render(scene, camera);
    }

    // Mouse controls
    function onMouseDown(e) {
        if (isSpinning) return;
        mouseDown = true;
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    }

    function onMouseMove(e) {
        if (!mouseDown || isSpinning) return;

        const deltaX = e.clientX - mouse.x;
        const deltaY = e.clientY - mouse.y;

        sphereGroup.rotation.y += deltaX * 0.005;
        sphereGroup.rotation.x += deltaY * 0.005;

        ballsGroup.rotation.y += deltaX * 0.005;
        ballsGroup.rotation.x += deltaY * 0.005;

        mouse.x = e.clientX;
        mouse.y = e.clientY;
    }

    function onMouseUp() {
        mouseDown = false;
    }

    function onWheel(e) {
        e.preventDefault();
        camera.position.z += e.deltaY * 0.01;
        camera.position.z = Math.max(8, Math.min(20, camera.position.z));
    }

    // Touch controls
    let touchStart = { x: 0, y: 0 };

    function onTouchStart(e) {
        if (isSpinning || e.touches.length !== 1) return;
        touchStart.x = e.touches[0].clientX;
        touchStart.y = e.touches[0].clientY;
    }

    function onTouchMove(e) {
        if (isSpinning || e.touches.length !== 1) return;
        e.preventDefault();

        const deltaX = e.touches[0].clientX - touchStart.x;
        const deltaY = e.touches[0].clientY - touchStart.y;

        sphereGroup.rotation.y += deltaX * 0.005;
        sphereGroup.rotation.x += deltaY * 0.005;

        ballsGroup.rotation.y += deltaX * 0.005;
        ballsGroup.rotation.x += deltaY * 0.005;

        touchStart.x = e.touches[0].clientX;
        touchStart.y = e.touches[0].clientY;
    }

    function updateRendererSize() {
        const container = document.getElementById('container');
        const containerWidth = container.clientWidth;
        const containerHeight = container.clientHeight;
        
        renderer.setSize(containerWidth, containerHeight);
        camera.aspect = containerWidth / containerHeight;
        camera.updateProjectionMatrix();
    }

    function onWindowResize() {
        updateRendererSize();
    }

    function moveWinnerBallToGrid(number) {
        if (!winnerBall) return;
        
        // Prevent multiple movements
        if (winnerBallMoving) return;
        
        winnerBallMoving = true;
        
        // Calculate target position in 3D space
        const targetCell = numbersGrid[number];
        if (!targetCell) return;
        
        const targetRect = targetCell.getBoundingClientRect();
        const containerRect = document.getElementById('container').getBoundingClientRect();
        
        // Convert screen coordinates to 3D world coordinates
        const x = ((targetRect.left + targetRect.width/2 - containerRect.left) / containerRect.width) * 2 - 1;
        const y = -((targetRect.top + targetRect.height/2 - containerRect.top) / containerRect.height) * 2 + 1;
        
        // Create target position in 3D space
        winnerBallTarget = new THREE.Vector3(x * 8, y * 6, 5);
        
        // Set movement parameters - faster
        winnerBall.userData.moveSpeed = 0.04;
        winnerBall.userData.moveProgress = 0;
    }

    function updateNumbersGrid(number) {
        const cell = numbersGrid[number];
        if (cell) {
            cell.classList.add('drawn');

            // Remove active class from all cells
            document.querySelectorAll('.number-cell').forEach(cell => {
                cell.classList.remove('active');
            });

            // Add active class to current number
            cell.classList.add('active');

            // Remove active class after 2 seconds
            setTimeout(() => {
                cell.classList.remove('active');
            }, 2000);
        }
    }

    init();
</script>
</body>
</html>
