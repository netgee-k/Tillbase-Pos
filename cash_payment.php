<!-- cash_payment.php -->
<h2>Cash Payment</h2>
<form action="cash_process.php" method="POST" id="cashForm">
    <label for="amount">Amount Paid:</label>
    <input type="number" name="amount" id="amount" required step="0.01">

    <label for="customer">Customer Name (optional):</label>
    <input type="text" name="customer" id="customer">

    <button type="submit">Pay & Print</button>
</form>
