<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>𝘼𝙡𝙖𝙙𝙙𝙞𝙣𝙣 𝙂𝙖𝙢𝙚 Self-service Center</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #b8b8fe;
            /* Light blush pink background */
            color: #000000;
            /* Black text color */
            display: flex;
            justify-content: center;
            padding: 20px;
            min-height: 50vh;
        }


        .container {
            width: 400px;
            background-color: #c4bce7;
            /* Dark black container */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .header-logo,
        .popup .header-logo {
            font-size: 2em;
            font-weight: bold;
            color: linear-gradient(90deg, #b8b8fe 0%, #9481ff 100%);
            /* Blush pink for logo */
            margin-bottom: 10px;
        }

        .header-title,
        .popup .header-title {
            font-size: 1.2em;
            color: linear-gradient(90deg, #b8b8fe 0%, #9481ff 100%);
            /* Blush pink for titles */
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            font-size: 1em;
            margin-bottom: 5px;
            display: block;
            color: #9481ff;
            /* Blush pink for labels */
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 1em;
            border: 1px solid #000000;
            /* Blush pink border */
            border-radius: 5px;
            background-color: #ffff;
            color: #000000;
            /* Light text color */
        }

        .additional-form {
            display: none;
        }

        .btn {
            width: 100%;
            padding: 12px;
            font-size: 1em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 10px;
            color: #1a1a1a;
            /* Dark text on button */
            background-color: linear-gradient(90deg, #b8b8fe 0%, #9481ff 100%);
            /* Blush pink button */
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #9481ff;
            /* Darker blush pink on hover */
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .popup {
            width: 350px;
            padding: 20px;
            background-color: #1a1a1a;
            /* Dark black popup */
            border-radius: 8px;
            text-align: center;
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        .popup-close {
            position: absolute;
            top: 10px;
            right: 10px;
            color: linear-gradient(90deg, #b8b8fe 0%, #9481ff 100%);
            /* Blush pink close button */
            cursor: pointer;
            font-size: 1.5em;
        }

        #issueResults {
            max-height: 200px;
            overflow-y: auto;
            text-align: left;
            margin-top: 20px;
            padding-right: 10px;
            color: linear-gradient(90deg, #b8b8fe 0%, #9481ff 100%);
            /* Blush pink for issue results */
        }

        #issueResults p {
            margin: 5px 0;
            line-height: 1.4;
        }

        #issueResults hr {
            border: 0.5px solid linear-gradient(90deg, #b8b8fe 0%, #9481ff 100%);
            /* Blush pink divider */
            margin: 8px 0;
        }
    </style>

    <!-- License Verification Script -->
    <!-- <script>
        const licenseKey = "D9WMUP8M79O1Z4MD"; // Replace with the actual license key from the database

        function verifyLicense() {
            fetch(`verify_license.php?license_key=${encodeURIComponent(licenseKey)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status !== "success") {
                        alert("Unauthorized Access: License validation failed or is invalid.");
                        document.body.innerHTML = "<h1>Unauthorized Access</h1><p>License validation failed.</p>";
                    } else {
                        document.body.style.visibility = 'visible'; // Show content if license is valid
                    }
                })
                .catch(error => {
                    console.error("License verification error:", error);
                    alert("Error: Could not verify license.");
                    document.body.innerHTML = "<h1>Unauthorized Access</h1><p>Could not verify license.</p>";
                });
        }

        // Run verification on page load
        window.onload = verifyLicense;
    </script> -->
</head>

<body>
    <div class="container">
        <div class="header-logo"><img height="200px" width="200px" src="https://joshgame.online/support/logo.png">
        </div>
        <div class="header-title">𝘼𝙡𝙖𝙙𝙙𝙞𝙣𝙣 𝙂𝙖𝙢𝙚 Self-service Center</div>

        <form action="submit_issue.php" method="POST" enctype="multipart/form-data"
            onsubmit="return confirmSubmission()">
            <div class="form-group">
                <label for="issue">Issue List:</label>
                <select id="issue" name="issue" onchange="toggleAdditionalForm()" required>
                    <option value="">Please select the issue you are applying for</option>
                    <option value="depositNotReceived">Deposit Not Received</option>
                    <option value="withdrawalProblem">Withdrawal Problem</option>
                    <option value="modifyBankAccount">Modify Bank Account</option>
                    <option value="changeBankAccount">Change Bank Account</option>
                    <option value="wingo1MinWinStreakBonus">Wingo 1 Min Win Streak Bonus</option>
                </select>
            </div>

            <div class="form-group">
                <label for="account">ID Account 𝘼𝙡𝙖𝙙𝙙𝙞𝙣𝙣 𝙂𝙖𝙢𝙚 ID:</label>
                <input type="text" id="account" name="account" placeholder="Enter ID account" required />
            </div>

            <!-- Deposit Not Received Form -->
            <div id="depositNotReceivedForm" class="additional-form">
                <div class="form-group">
                    <label for="amountDeposit">Amount Deposit:</label>
                    <input type="text" id="amountDeposit" name="amountDeposit" placeholder="Enter Amount Deposit" />
                </div>
                <div class="form-group">
                    <label for="utrNumber">UTR Number:</label>
                    <input type="text" id="utrNumber" name="utrNumber" placeholder="Enter UTR Number" />
                </div>
                <div class="form-group">
                    <label for="upiid">Receiver UPI ID:</label>
                    <input type="text" id="upiid" name="upiid" placeholder="Enter Receiver UPI ID" />
                </div>
                <div class="form-group">
                    <label for="depositProof">Deposit Proof Receipt:</label>
                    <input type="file" id="depositProof" name="depositProof" />
                </div>
                <div class="form-group">
                    <label for="orderNumber">Order Number:</label>
                    <input type="text" id="orderNumber" name="orderNumber" placeholder="Enter Order Number" />
                </div>
            </div>

            <!-- Withdrawal Problem Form -->
            <div id="withdrawalProblemForm" class="additional-form">
                <div class="form-group">
                    <label for="withdrawalOrderNumber">Withdrawal Order Number:</label>
                    <input type="text" id="withdrawalOrderNumber" name="withdrawalOrderNumber"
                        placeholder="Enter Withdrawal Order Number" />
                </div>
                <div class="form-group">
                    <label for="withdrawalAmount">Withdrawal Amount:</label>
                    <input type="text" id="withdrawalAmount" name="withdrawalAmount"
                        placeholder="Enter Withdrawal Amount" />
                </div>
            </div>

            <!-- Modify Bank Account Form -->
            <div id="modifyBankAccountForm" class="additional-form">
                <div class="form-group">
                    <label for="screenshot">Screenshot of 𝘼𝙡𝙖𝙙𝙙𝙞𝙣𝙣 𝙂𝙖𝙢𝙚 ID:</label>
                    <input type="file" id="screenshot" name="screenshot" />
                </div>
                <div class="form-group">
                    <label for="identificationCard">Photo of Identification Card:</label>
                    <input type="file" id="identificationCard" name="identificationCard" />
                </div>
                <div class="form-group">
                    <label for="bankAccountPhoto">Photo of Bank Account:</label>
                    <input type="file" id="bankAccountPhoto" name="bankAccountPhoto" />
                </div>
                <div class="form-group">
                    <label for="bankName">Bank Name:</label>
                    <input type="text" id="bankName" name="bankName" placeholder="Enter Bank Name" />
                </div>
                <div class="form-group">
                    <label for="bankAccountHolder">Bank Account Holder Name:</label>
                    <input type="text" id="bankAccountHolder" name="bankAccountHolder"
                        placeholder="Enter Bank Account Holder Name" />
                </div>
                <div class="form-group">
                    <label for="bankAccountNumber">Bank Account Number:</label>
                    <input type="text" id="bankAccountNumber" name="bankAccountNumber"
                        placeholder="Enter Bank Account Number" />
                </div>
                <div class="form-group">
                    <label for="ifscCode">IFSC Code:</label>
                    <input type="text" id="ifscCode" name="ifscCode" placeholder="Enter IFSC Code" />
                </div>
                <div class="form-group">
                    <label for="phoneNumber">Phone Number:</label>
                    <input type="text" id="phoneNumber" name="phoneNumber" placeholder="Enter Phone Number" />
                </div>
            </div>

            <!-- Change Bank Account Form -->
            <div id="changeBankAccountForm" class="additional-form">
                <div class="form-group">
                    <label for="screenshot">Screenshot of 𝘼𝙡𝙖𝙙𝙙𝙞𝙣𝙣 𝙂𝙖𝙢𝙚 ID:</label>
                    <input type="file" id="screenshot" name="screenshot" />
                </div>
                <div class="form-group">
                    <label for="identificationCard">Photo of Identification Card:</label>
                    <input type="file" id="identificationCard" name="identificationCard" />
                </div>
                <div class="form-group">
                    <label for="oldBankPassbook">Photo of Old Bank Passbook:</label>
                    <input type="file" id="oldBankPassbook" name="oldBankPassbook" />
                </div>
                <div class="form-group">
                    <label for="newBankPassbook">Photo of New Bank Passbook:</label>
                    <input type="file" id="newBankPassbook" name="newBankPassbook" />
                </div>
                <div class="form-group">
                    <label for="latestDepositProof">Latest Deposit Receipt Proof:</label>
                    <input type="file" id="latestDepositProof" name="latestDepositProof" />
                </div>
                <div class="form-group">
                    <label for="bankName">Bank Name:</label>
                    <input type="text" id="bankName" name="bankName" placeholder="Enter Bank Name" />
                </div>
                <div class="form-group">
                    <label for="bankAccountHolder">Bank Account Holder Name:</label>
                    <input type="text" id="bankAccountHolder" name="bankAccountHolder"
                        placeholder="Enter Bank Account Holder Name" />
                </div>
                <div class="form-group">
                    <label for="bankAccountNumber">Bank Account Number:</label>
                    <input type="text" id="bankAccountNumber" name="bankAccountNumber"
                        placeholder="Enter Bank Account Number" />
                </div>
                <div class="form-group">
                    <label for="ifscCode">IFSC Code:</label>
                    <input type="text" id="ifscCode" name="ifscCode" placeholder="Enter IFSC Code" />
                </div>
                <div class="form-group">
                    <label for="phoneNumber">Phone Number:</label>
                    <input type="text" id="phoneNumber" name="phoneNumber" placeholder="Enter Phone Number" />
                </div>
                <div class="form-group">
                    <label for="reasonForChange">Reason for Changing Bank Details:</label>
                    <input type="text" id="reasonForChange" name="reasonForChange"
                        placeholder="Enter the Reason for Changing Bank Details" />
                </div>
            </div>

            <!-- Wingo 1 Min Win Streak Bonus Form -->
            <div id="wingo1MinWinStreakBonusForm" class="additional-form">
                <div class="form-group">
                    <label for="winingStartPeriodNo">Start of winning period:</label>
                    <input type="text" id="winingStartPeriodNo" name="winingStartPeriodNo"
                        placeholder="Enter Start of winning period" />
                </div>
                <div class="form-group">
                    <label for="winingEndPeriodNo">End of winning period:</label>
                    <input type="text" id="winingEndPeriodNo" name="winingEndPeriodNo"
                        placeholder="Enter End of winning period" />
                </div>
                <div class="form-group">
                    <label for="consecutiveWinStreak">Consecutive Win Streak Times (8/18/28/38/48):</label>
                    <input type="text" id="consecutiveWinStreak" name="consecutiveWinStreak"
                        placeholder="Consecutive Win Streak Times" />
                </div>
            </div>

            <button type="submit" class="btn">Submit Issue</button>
            <button type="button" class="btn" onclick="openPopup()">Check Issue Status</button>
        </form>
    </div>

    <!-- Overlay for Popup -->
    <div id="overlay" class="overlay">
        <div class="popup">
            <div class="popup-close" onclick="closePopup()">×</div>
            <div class="header-logo"><img height="100px" width="100px"
                    src="https://𝘼𝙡𝙖𝙙𝙙𝙞𝙣𝙣 𝙂𝙖𝙢𝙚.com/support/logo.png"> </div>
            <div class="header-title">Issue Progress</div>
            <div class="form-group">
                <label for="accountSearch">Enter ID Account:</label>
                <input type="text" id="accountSearch" placeholder="Enter ID account" />
            </div>
            <div class="form-group">
                <label for="issueType">Issue Type:</label>
                <select id="issueType">
                    <option value="">All Issues</option>
                    <option value="depositNotReceived">Deposit Not Received</option>
                    <option value="withdrawalProblem">Withdrawal Problem</option>
                    <option value="modifyBankAccount">Modify Bank Account</option>
                    <option value="changeBankAccount">Change Bank Account</option>
                    <option value="wingo1MinWinStreakBonus">Wingo 1 Min Win Streak Bonus</option>
                </select>
            </div>
            <button onclick="searchIssues()" class="btn">Click to search</button>
            <div id="issueResults"></div>
        </div>
    </div>

    <script>
        function toggleAdditionalForm() {
            const forms = document.querySelectorAll('.additional-form');
            forms.forEach(form => form.style.display = 'none');

            const selectedIssue = document.getElementById('issue').value;
            const formToShow = document.getElementById(`${selectedIssue}Form`);
            if (formToShow) formToShow.style.display = 'block';
        }

        function confirmSubmission() {
            return confirm("Are you sure you want to submit this issue?");
        }

        function openPopup() {
            document.getElementById('overlay').style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('overlay').style.display = 'none';
        }

        function searchIssues() {
            const accountID = document.getElementById('accountSearch').value.trim();
            const issueType = document.getElementById('issueType').value;

            let url = `fetch_issues.php?account_id=${encodeURIComponent(accountID)}`;
            if (issueType) {
                url += `&issue_type=${encodeURIComponent(issueType)}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => displayIssues(data))
                .catch(error => console.error('Error:', error));
        }

        function displayIssues(issues) {
            const issueResults = document.getElementById('issueResults');
            issueResults.innerHTML = issues.length === 0 ? '<p>No issues found.</p>' : '';

            issues.forEach(issue => {
                const issueElement = document.createElement('div');
                issueElement.innerHTML = `
                    <p><strong>Issue Type:</strong> ${issue.issue_type}</p>
<p><strong>Amount:</strong> ${issue.issue_type === 'withdrawalProblem' ? (issue.withdrawal_amount || 'N/A') : (issue.amount_deposit || 'N/A')}</p>

                    <p><strong>Status:</strong> ${issue.status || 'Pending'}</p>
                    <hr>
                `;
                issueResults.appendChild(issueElement);
            });
        }
    </script>
</body>

</html>