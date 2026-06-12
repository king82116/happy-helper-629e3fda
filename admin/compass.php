<?php
// Your existing PHP code to fetch user permissions. This remains unchanged.
$chkserial = mysqli_query($conn, "select * from `nirvahaka_shonu` where `unohs`='" . $_SESSION['unohs'] . "'");
$salu = mysqli_fetch_array($chkserial);
$dashboard = $salu['dashboard'];
$wingomanager = $salu['wingomanager'];
$k3manager = $salu['k3manager'];
$d5manager = $salu['5dmanager'];
$finance = $salu['finance'];
$managegame = $salu['managegame'];
$support = $salu['support'];
?>

<!-- =================================================================
     NEW ENHANCED STYLESHEET
     ================================================================= -->
<style>
    /* Overall navigation container styling */
    .nav {
        background-color: #ffad00;
        /* A deep, dark gold for the main background */
        padding: 5px;
        border-radius: 12px;
        font-family: 'Arial', sans-serif;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    /* Styling for each navigation item */
    .nav-item {
        margin: 5px 0;
    }

    /* General styles for all navigation links (main and sub-menu) */
    .nav-link {
        display: flex;
        /* Use flexbox for better alignment of icon and text */
        align-items: center;
        padding: 5px 5px;
        border-radius: 8px;
        transition: all 0.3s ease-in-out;
        font-weight: 500;
        /* Slightly bolder text */
        position: relative;
    }

    .nav-link i.menu-icon,
    .nav-link i.nav-icon {
        margin-right: 8px;
        min-width: 18px;
        flex-shrink: 0;
        text-align: center;
    }

    .nav-link .menu-title {
        white-space: normal;
        word-break: break-word;
        line-height: 1.3;
    }

    /* ---- Main Menu Links ---- */

    /* Styles for the top-level menu items */
    ul.nav>.nav-item>.nav-link {
        font-size: 14px;
        /* Smaller text for main menu */
        color: #ffad00;
        /* Dark text for contrast on gold background */
        background: linear-gradient(135deg, #FFD700, #FBB03B);
        /* Gold to orange-yellow gradient */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Hover effect for main menu links */
    ul.nav>.nav-item>.nav-link:hover {
        color: #ffff;
        transform: translateY(-2px);
        /* Subtle lift effect */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
        background: linear-gradient(135deg, #ffe033, #ffc15e);
        /* Brighter gradient on hover */
    }

    /* ---- Dropdown (Sub-Menu) Styles ---- */

    /* Container for the dropdown menu */
    .collapse {
        margin-top: 5px;
        padding-left: 0px;
        /* Indent sub-menu items */
    }

    .sub-menu {
        background: rgba(0, 0, 0, 0.2);
        /* Semi-transparent dark background */
        border-radius: 5px;
        padding: 0px;
    }

    /* Links inside the dropdown */
    .collapse .nav-link {
        font-size: 13px;
        /* Even smaller text for sub-menu items */
        color: #FFD700;
        /* Gold color text */
        padding: 2px 10px;
        margin: 2px 0;
        background-color: transparent;
        /* No background color initially */
        border-left: 3px solid transparent;
        /* For hover accent */
    }

    /* Hover effect for dropdown links */
    .collapse .nav-link:hover {
        background-color: rgba(255, 215, 0, 0.15);
        /* Faint gold highlight */
        color: #FFFFFF;
        /* White text on hover for pop */
        border-left: 3px solid #FFD700;
        /* Gold accent bar */
    }

    .menu-title {
        flex-grow: 1;
        /* Allows title to take available space */
    }

    /* Arrow icon for dropdowns */
    .menu-arrow {
        transition: transform 0.3s ease;
    }

    /* Rotate arrow when the dropdown is open */
    .nav-link[aria-expanded="true"] .menu-arrow {
        transform: rotate(90deg);
    }
</style>


<!-- =================================================================
     UNCHANGED HTML AND PHP STRUCTURE
     ================================================================= -->
<ul class="nav">
    <li class="nav-item">
        <a class="nav-link" href="dashboard.php">
            <i class="icon-box menu-icon"></i>
            <span class="menu-title">Dashboard</span>
        </a>
    </li>
    <?php if ($wingomanager == 1): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                <i class="icon-disc menu-icon"></i>
                <span class="menu-title">WinGo Manager</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="wingo10min.php">WinGo 30 sec</a></li>
                    <li class="nav-item"> <a class="nav-link" href="wingo1min.php">WinGo 1 Min</a></li>
                    <li class="nav-item"> <a class="nav-link" href="wingo3min.php">WinGo 3 Min</a></li>
                    <li class="nav-item"> <a class="nav-link" href="wingo5min.php">WinGo 5 Min</a></li>
                </ul>
            </div>
        </li>
    <?php endif; ?>
    <?php if ($k3manager == 1): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic-1" aria-expanded="false" aria-controls="ui-basic-1">
                <i class="icon-disc menu-icon"></i>
                <span class="menu-title">K3 Manager</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic-1">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="k31min.php">K3 1 Min</a></li>
                    <li class="nav-item"> <a class="nav-link" href="k33min.php">K3 3 Min</a></li>
                    <li class="nav-item"> <a class="nav-link" href="k35min.php">K3 5 Min</a></li>
                    <li class="nav-item"> <a class="nav-link" href="k310min.php">K3 10 Min</a></li>
                </ul>
            </div>
        </li>
    <?php endif; ?>
    <?php if ($d5manager == 1): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic-2" aria-expanded="false" aria-controls="ui-basic-2">
                <i class="icon-disc menu-icon"></i>
                <span class="menu-title">5D Manager</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic-2">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="5d1min.php">5D 1 Min</a></li>
                    <li class="nav-item"> <a class="nav-link" href="5d3min.php">5D 3 Min</a></li>
                    <li class="nav-item"> <a class="nav-link" href="5d5min.php">5D 5 Min</a></li>
                    <li class="nav-item"> <a class="nav-link" href="5d10min.php">5D 10 Min</a></li>
                </ul>
            </div>
        </li>
    <?php endif; ?>
    <?php if ($finance == 1): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic-3" aria-expanded="false" aria-controls="ui-basic-3">
                <i class="icon-book menu-icon"></i>
                <span class="menu-title">Finance</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic-3">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="addupi.php">Add Upi</a></li>
                    <li class="nav-item"> <a class="nav-link" href="usdtkids.php">USDT RATE </a></li>
                    <li class="nav-item"> <a class="nav-link" href="addusdt.php">Add Usdt</a></li>
                    <li class="nav-item"> <a class="nav-link" href="addupiimg.php">Add Upi Image</a></li>
                    <li class="nav-item"> <a class="nav-link" href="addimgusdt_2.php">Add Usdt Image</a></li>
                    <li class="nav-item"> <a class="nav-link" href="deposit_update.php">Deposit Update</a></li>
                    <li class="nav-item"> <a class="nav-link" href="manual_user_deposit.php">Manual User Deposit</a></li>
                    <li class="nav-item"> <a class="nav-link" href="manage_withdraw.php">Withdraw Apply</a></li>
                    <li class="nav-item"> <a class="nav-link" href="withdraw_accept_list.php">Withdraw Sent</a></li>
                    <li class="nav-item"> <a class="nav-link" href="withdraw_reject_list.php">Withdraw Reject</a></li>
                    <li class="nav-item"> <a class="nav-link" href="update_with_pay.php">Update Gateway</a></li>
                </ul>
            </div>
        </li>
    <?php endif; ?>
    <?php if ($support == 1): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic-5" aria-expanded="false" aria-controls="ui-basic-5">
                <i class="icon-book menu-icon"></i>
                <span class="menu-title">Support</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic-5">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="deposite.php">Dashboard</a></li>
                    <li class="nav-item"> <a class="nav-link" href="withprob.php">Withdrawal Problem</a></li>
                    <li class="nav-item"> <a class="nav-link" href="ifscm.php">IFSC Modification</a></li>
                    <li class="nav-item"> <a class="nav-link" href="bankm.php">Bank Modification</a></li>
                    <li class="nav-item"> <a class="nav-link" href="gamep.php">Game Problem</a></li>
                </ul>
            </div>
        </li>
    <?php endif; ?>
    <?php if ($managegame == 1): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic-9" aria-expanded="false" aria-controls="ui-basic-9">
                <i class="icon-disc menu-icon"></i>
                <span class="menu-title">Extra Settings</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic-9">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="DKH/upline_chain.php">Upline Chain</a></li>
                    <li class="nav-item"> <a class="nav-link" href="DKH/fully_detailed_subdata.php">Subordinate Data</a>
                    </li>
                    <li class="nav-item"> <a class="nav-link" href="DKH/balance_detuction.php">Balance Deduction</a></li>
                    <li class="nav-item"> <a class="nav-link" href="DKH/kacha_chitha_of_user.php">Users Behn Yakhii</a></li>
                    <li class="nav-item"> <a class="nav-link" href="DKH/manage_announcements.php">Manage Notification</a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic-4" aria-expanded="false" aria-controls="ui-basic-4">
                <i class="icon-head menu-icon"></i>
                <span class="menu-title">Manage Game</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic-4">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="userbonus.php">Bonus Manage</a></li>
                    <li class="nav-item"> <a class="nav-link" href="users_deposit_downline.php">User Manage</a></li>
                    <li class="nav-item"> <a class="nav-link" href="teamdayreport.php">Team Report Manage</a></li>
                    <li class="nav-item"> <a class="nav-link" href="illegal_bet_ban.php">Manage Illegal Bet</a></li>
                    <li class="nav-item"> <a class="nav-link" href="manage_bet.php">Manage Need to Bet</a></li>
                    <li class="nav-item"> <a class="nav-link" href="manage_bankcard.php">Modify Bank Details</a></li>
                    <li class="nav-item"> <a class="nav-link" href="adminpass.php">Admin Password</a></li>
                    <li class="nav-item"> <a class="nav-link" href="autobanuser.php">Check Same IP</a></li>
                    <li class="nav-item"> <a class="nav-link" href="mainkids.php">Maintenance</a></li>
                    <li class="nav-item"> <a class="nav-link" href="banbybet.php">Ban Illegal Users</a></li>
                    <li class="nav-item"> <a class="nav-link" href="updatejet.php">Jet Max Value</a></li>
                    <li class="nav-item"> <a class="nav-link" href="manage_support.php">Users Query</a></li>
                    <li class="nav-item"> <a class="nav-link" href="manage_user.php">Users</a></li>
                    <li class="nav-item"> <a class="nav-link" href="manage_salary.php">Daily Salary</a></li>
                    <li class="nav-item"> <a class="nav-link" href="addgiftcode.php">Gift Code</a></li>
                    <li class="nav-item"> <a class="nav-link" href="addtelegram.php">Telegram</a></li>
                    <li class="nav-item"> <a class="nav-link" href="addadmin.php">Add Admin</a></li>
                    <li class="nav-item"> <a class="nav-link" href="demouser.php">Demo User</a></li>
                    <li class="nav-item"> <a class="nav-link" href="agentuser.php">Agent User</a></li>
                </ul>
            </div>
        </li>
    <?php endif; ?>
    <li class="nav-item">
        <a href="https://joshgame.online" class="nav-link">
            <i class="nav-icon fa fa-sign-out" aria-hidden="true"></i>
            <span class="menu-title">Go To Website</span>
        </a>
    </li>
</ul>