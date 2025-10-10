<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bingo</title>
    <style>
        body {
            margin: 0;
            overflow: hidden;
            background: #000000;
            font-family: 'Arial', sans-serif;
        }

        #container {
            width: 100vw;
            height: 100vh;
        }

        #controls {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(255, 255, 255, 0.3);
            text-align: center;
            z-index: 10;
        }

        button {
            background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
            color: #333;
            border: 2px solid #333;
            padding: 12px 30px;
            margin: 5px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
        }

        button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.5);
        }

        button:active {
            transform: scale(0.95);
        }

        #result {
            position: absolute;
            bottom: 30px;
            left: 50%;
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
    </style>
</head>
<body>
<div id="container"></div>
<div id="controls">
    <button onclick="startSpin()">🎲 QUAY SỐ</button>
    <button onclick="resetSphere()">🔄 RESET</button>
</div>
<div id="result"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
    let scene, camera, renderer, sphereGroup, ballsGroup;
    let balls = [];
    let rotationSpeed = { x: 0, y: 0 };
    let isSpinning = false;
    let winnerBall = null;
    let mouse = { x: 0, y: 0 };
    let mouseDown = false;

    function init() {
        // Scene
        scene = new THREE.Scene();
        scene.background = new THREE.Color(0x000000);

        // Camera
        camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 12;

        // Renderer
        renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
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
            color      : 0xffffff, // màu xanh ngọc nổi bật
            linewidth  : 3,
            transparent: true,
            opacity    : 0.8
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
                number    : i,
                initialPos: ballGroup.position.clone(),
                velocity  : new THREE.Vector3((Math.random() - 0.5) * 0.03, (Math.random() - 0.5) * 0.03, (Math.random() - 0.5) * 0.03),
                isFalling : false,
            };

            ballsGroup.add(ballGroup);
            balls.push(ballGroup);
        }
    }


    function startSpin() {
        if (isSpinning) return;

        isSpinning = true;
        document.getElementById('result').style.display = 'none';
        document.getElementById('info').style.display = 'none';

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

        rotationSpeed.x = (Math.random() - 0.5) * 0.5;
        rotationSpeed.y = (Math.random() - 0.5) * 0.5;

        // Stop after 4 seconds and pick winner
        setTimeout(() => {
            pickWinner();
        }, 4000);
    }

    function pickWinner() {
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

            // Set fall velocity toward camera
            const direction = worldPos.clone().normalize();
            winner.userData.fallVelocity = direction.multiplyScalar(0.15);
            winner.userData.fallVelocity.y -= 0.05;
            winner.userData.rotationSpeed = {
                x: Math.random() * 0.2 - 0.1,
                y: Math.random() * 0.2 - 0.1,
                z: Math.random() * 0.2 - 0.1
            };


            const result = document.getElementById('result');
            result.textContent = `🎉 SỐ TRÚNG THƯỞNG: ${winner.userData.number}`;
            result.style.display = 'block';
        }
    }

    function resetSphere() {
        rotationSpeed.x = 0;
        rotationSpeed.y = 0;
        sphereGroup.rotation.set(0, 0, 0);
        ballsGroup.rotation.set(0, 0, 0);
        isSpinning = false;
        document.getElementById('result').style.display = 'none';
        document.getElementById('info').style.display = 'block';

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

    function animate() {
        requestAnimationFrame(animate);

        if (isSpinning) {
            sphereGroup.rotation.x += rotationSpeed.x;
            sphereGroup.rotation.y += rotationSpeed.y;

            ballsGroup.rotation.x += rotationSpeed.x;
            ballsGroup.rotation.y += rotationSpeed.y;

            // Slow down
            rotationSpeed.x *= 0.985;
            rotationSpeed.y *= 0.985;

            // Make balls bounce more during spin
            balls.forEach(ball => {
                if (!ball.userData.isFalling) {
                    ball.position.add(ball.userData.velocity);

                    const distance = ball.position.length();
                    if (distance > 5.2) {
                        const normal = ball.position.clone().normalize();
                        ball.userData.velocity.reflect(normal);
                        ball.userData.velocity.multiplyScalar(0.9);
                        ball.position.setLength(5.2);
                    }

                    // Add chaos during spin
                    ball.userData.velocity.x += (Math.random() - 0.5) * 0.02;
                    ball.userData.velocity.y += (Math.random() - 0.5) * 0.02;
                    ball.userData.velocity.z += (Math.random() - 0.5) * 0.02;

                    // Limit velocity
                    const speed = ball.userData.velocity.length();
                    if (speed > 0.15) {
                        ball.userData.velocity.setLength(0.15);
                    }
                }
            });
        } else if (!winnerBall) {
            // Gentle idle rotation
            sphereGroup.rotation.y += 0.003;
            ballsGroup.rotation.y += 0.003;

            // Gentle ball movement
            balls.forEach(ball => {
                if (!ball.userData.isFalling) {
                    ball.position.add(ball.userData.velocity);

                    const distance = ball.position.length();
                    if (distance > 5.2) {
                        const normal = ball.position.clone().normalize();
                        ball.userData.velocity.reflect(normal);
                        ball.userData.velocity.multiplyScalar(0.8);
                        ball.position.setLength(5.2);
                    }
                }
            });
        }

        // Animate falling winner
        if (winnerBall && winnerBall.userData.isFalling) {
            winnerBall.position.add(winnerBall.userData.fallVelocity);
            winnerBall.userData.fallVelocity.y -= 0.003; // gravity

            // Scale up the ball
            const scale = Math.min(winnerBall.scale.x + 0.015, 3);
            winnerBall.scale.set(scale, scale, scale);

            // Rotate the ball
            winnerBall.rotation.x += winnerBall.userData.rotationSpeed.x;
            winnerBall.rotation.y += winnerBall.userData.rotationSpeed.y;
            winnerBall.rotation.z += winnerBall.userData.rotationSpeed.z;
            if (winnerBall.position.z > 15 || winnerBall.position.y < -10) {
                scene.remove(winnerBall);
                balls = balls.filter(b => b !== winnerBall);
                winnerBall = null;
            }
        }

        // Make all balls face camera (except falling one)
        balls.forEach(ball => {
            if (!ball.userData.isFalling) {
                ball.lookAt(camera.position);
            }
        });

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

    function onWindowResize() {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    }

    init();
</script>
</body>
</html>
