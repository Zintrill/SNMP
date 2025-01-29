SNMP Dashboard - Network Device Monitoring and Management Application
SNMP Dashboard is a web-based network monitoring application that allows users to efficiently track, 
manage, and visualize the status of SNMP-enabled devices. The application provides real-time data monitoring, 
device availability checking through ICMP pinging, and comprehensive network insights using interactive charts.

This project is built with Symfony, PostgreSQL, Docker, and Chart.js, ensuring scalability, security, and ease of deployment. 
The intuitive dashboard offers a user-friendly experience for network administrators, IT professionals, and engineers who need a centralized view of their network infrastructure.


📡 SNMP Device Monitoring
Retrieve and display the real-time status of network devices.
Identify whether a device is Online, Offline, or Waiting.
Support for various SNMP device types such as routers, switches, computers, printers, and more.

📊 Interactive Charts & Data Visualization
Doughnut Charts to compare the number of Online, Offline, and Waiting devices.
Line Chart to track historical device status changes over time.
Automated data refresh every 5 seconds, ensuring up-to-date insights.

🌐 Device Availability Checks with ICMP Ping
Execute single-packet ping requests to minimize network load.
Detect and update device statuses based on ping responses.
Real-time updates reflecting the latest device connectivity status.

🔑 User Management & Role-Based Access Control
Admin & Operator roles for secure access control.
Admins can manage devices, view statistics, and configure network settings.
Operators can monitor device statuses and review network performance.

⚙️ SNMP Configuration & Device Management
View and edit SNMP settings for each device.
Update device names, IP addresses, SNMP versions, and authentication details.
Easily integrate with custom network management workflows.

🚀 Fast & Scalable Deployment
Docker-based deployment for simple setup and containerized execution.
PostgreSQL database for robust and scalable data storage.
Optimized API endpoints to ensure efficient data retrieval and updates.

🛠️ Technologies Used
Backend: Symfony (PHP 8.3.7, Doctrine ORM)
Database: PostgreSQL
Frontend: JavaScript (Chart.js), (linear-Chart.js) Twig Templates
Networking: SNMP, ICMP Ping
Containerization: Docker & Docker Compose
Security: Role-Based Access Control, Authentication System

![image](https://github.com/user-attachments/assets/8db1388d-0dd8-488d-96db-7281e6f29fbe)
![image](https://github.com/user-attachments/assets/de40e645-f3ed-429a-ba34-859968c46f64)
![image](https://github.com/user-attachments/assets/0b14080d-ccc1-446c-a655-7e8bac0d138e)
![image](https://github.com/user-attachments/assets/e85595df-a9bb-41eb-b01f-68d94c750f15)
![image](https://github.com/user-attachments/assets/c90305cc-91d7-436c-9794-0a18043d2fcd)
![image](https://github.com/user-attachments/assets/9494a3e5-86ee-4fbf-818c-a6d74fcabd16)
![image](https://github.com/user-attachments/assets/47298195-cf30-46ab-b599-83bfb0e21e89)
![image](https://github.com/user-attachments/assets/7c461f3d-d5a4-4d85-adb0-1a504a5101be)
![image](https://github.com/user-attachments/assets/ddd8ee5c-6f86-4d06-9e57-2f6babeed42b)
![image](https://github.com/user-attachments/assets/bc327b9f-5c2e-42e8-92c7-8c8ffdb092d8)
![image](https://github.com/user-attachments/assets/2f8edc66-7746-46fa-a5c1-f2879d2c2101)
![image](https://github.com/user-attachments/assets/bcac5dcf-d9d2-42e6-867d-94226b52535a)
