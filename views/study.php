<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Insigtful-Study</title>
    <link rel="icon" type="image/png" href="/public/images/logo.png">

    <style>
        :root {
            --dark-blue: #012169;
            --mint-green: #00af50;
            --transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--dark-blue);
            color: white;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 30px;
            left: 30px;
            background-color: transparent;
            color: white;
            border: 2px solid var(--mint-green);
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
            backdrop-filter: blur(4px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .back-btn:hover {
            background-color: var(--mint-green);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 175, 80, 0.4);
        }

        .container {
            text-align: center;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeIn 1.2s ease-out forwards;
            z-index: 10;
        }

        h1 {
            font-size: 4rem;
            margin-bottom: 1rem;
            letter-spacing: 4px;
            color: white;
            text-shadow: 0 0 10px rgba(0, 175, 80, 0.3);
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 600px;
            line-height: 1.6;
        }

        .loader {
            width: 60px;
            height: 60px;
            border: 6px solid var(--mint-green);
            border-top: 6px solid transparent;
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
            margin: 2rem auto;
        }

        .email-input {
            padding: 12px 20px;
            width: 300px;
            max-width: 90vw;
            border: none;
            border-radius: 30px;
            outline: none;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
            transition: var(--transition);
            margin-bottom: 1rem;
        }

        .email-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .email-input:focus {
            background-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 20px rgba(0, 175, 80, 0.4);
        }

        .btn {
            background-color: var(--mint-green);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0, 175, 80, 0.3);
        }

        .btn:hover {
            background-color: #009040;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 175, 80, 0.5);
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.1;
            }

            100% {
                transform: scale(1.2);
                opacity: 0.15;
            }
        }

        /* Responsive */
        @media (max-width: 600px) {
            h1 {
                font-size: 2.5rem;
            }

            .back-btn {
                top: 20px;
                left: 20px;
                padding: 8px 16px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <a href="index.php" class="back-btn">← Quay lại</a>
   
    <div class="container">
        <h1>COMING SOON</h1>
        <p>Chúng tôi đang xây dựng điều gì đó tuyệt vời. Hãy đăng ký để nhận thông báo khi ra mắt!</p>

        <input type="email" class="email-input" placeholder="Nhập email của bạn..." />

        <button class="btn">Thông báo cho tôi</button>

        <div class="loader"></div>
    </div>

     <script>
    document.addEventListener("DOMContentLoaded", () => {
      const btn = document.querySelector(".btn");
      const input = document.querySelector(".email-input");

      btn.addEventListener("click", () => {
        if (input.value) {
          alert("Cảm ơn bạn đã đăng ký!");
          input.value = "";
        } else {
          input.style.borderColor = "red";
          setTimeout(() => {
            input.style.borderColor = "";
          }, 1000);
        }
      });
    });
  </script>
</body>

</html>