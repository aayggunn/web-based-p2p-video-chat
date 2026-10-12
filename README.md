# 0megle.online - P2P Random Video Chat Platform

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![WebRTC](https://img.shields.io/badge/WebRTC-333333?style=for-the-badge&logo=webrtc&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap_5-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

0megle.online is a modern, secure, and completely free random video chat platform that connects people instantly from all around the world. It was developed as a modern alternative following the shutdown of popular chat networks. 

The project ensures high performance and strict privacy by transmitting media directly peer-to-peer (P2P) without routing through any central servers.

## 🚀 Key Features

* **End-to-End P2P Communication (WebRTC):** Users' audio and video data never pass through our servers. Direct browser-to-browser transmission is seamlessly established using PeerJS.
* **Anti-Deadlock Matchmaking Algorithm:** To prevent database deadlocks during thousands of concurrent connection requests, a custom PHP/MySQL architecture is implemented. When matching two users, the rows locked via `FOR UPDATE` are always ordered from the "smallest ID to the largest ID", reducing the risk of deadlocks to 0%.
* **Modern and Futuristic UI:** Offers a flawless user experience with a custom "Glassmorphism" design built on top of the Bootstrap 5 framework.
* **Secure Authentication:** Secure membership system with `password_hash` encryption and email verification integration via PHPMailer (robust bot and spam protection).
* **Zombie Connection Cleanup:** When users close the tab, an asynchronous farewell beacon is sent via `navigator.sendBeacon`, keeping the server's matchmaking queue clean and optimized.

## 🛠️ Technologies Used

* **Backend:** PHP 8+, MySQL (InnoDB)
* **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5
* **Real-Time Communication:** WebRTC, PeerJS
* **Security & Mail:** Prepared Statements (SQLi Protection), PHPMailer
* **SEO:** Schema Markup, Open Graph, Twitter Cards

## 📸 Screenshots

*(You can add your project screenshots here)*
> `![Home Page](link-here)`
> `![Chat Room](link-here)`

## ⚙️ Installation and Setup

To run the project on your local server (XAMPP/MAMP etc.) or hosting environment, follow these steps:

1. **Clone the Repo:**
   ```bash
   git clone [https://github.com/aayggunn/web-based-p2p-video-chat.git](https://github.com/aayggunn/web-based-p2p-video-chat.git)
