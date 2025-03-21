<?php
// Get the current page name to highlight active link
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Bottom Dock Navigation -->
<div class="bottom-dock">
    <nav class="bottom-dock-nav">
        <a href="dashboard.php" class="dock-item <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="community.php" class="dock-item <?php echo $current_page === 'community.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Community</span>
        </a>
        <a href="group-chat.php" class="dock-item <?php echo $current_page === 'group-chat.php' ? 'active' : ''; ?>">
            <i class="fas fa-comments"></i>
            <span>Group Chat</span>
        </a>
        <a href="support.php" class="dock-item <?php echo $current_page === 'support.php' ? 'active' : ''; ?>">
            <i class="fas fa-heart"></i>
            <span>Support Us</span>
        </a>
        <a href="profile.php" class="dock-item <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>
</div> 