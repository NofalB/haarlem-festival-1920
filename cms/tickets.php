<?php 
    if (require_once(__DIR__ . '/includes/admin-check.php')) {
?>
<html lang="en">
    <head>
        <?php include(__DIR__ . '/includes/header.php'); ?>
        <title>CMS – Tickets</title>
    </head>
    <body>
        <?php 
            include(__DIR__ . '/includes/sidebar.php'); 
            include(__DIR__ . '/../service/ticket-service.php'); 
            $ticketService = ticketService::getInstance();
            $ticket;
            if (isset($_GET['ticketuid'])) {
                $ticket = $ticketService->getTicket($_GET['ticketuid']);
            }
        ?>
        <h1 id="title-text">Scan Ticket</h1>
        <div id="edit-container">
            <form action="../controller/ticket-controller.php" method="post" name="scan-ticket">
                <div class="textbox-area">
                    <label class="textbox-label">Ticket Number</label>
                    <input type="text" name="ticket-uid" value="<?= $_GET['ticketuid'] ?? '' ?>">
                </div>
                <input type="submit" name="confirm-edit-ticket" value="Edit Ticket">
                <input type="hidden" name="ticket-uuid-original" value="<?= $_GET['ticketuid'] ?? '' ?>"/>
                <input type="submit" class="button-right" name="confirm-scan-ticket" value="Scan">
                <?php if (isset($_GET['ticketuid'])) { ?>
                    <div class="textbox-area">
                        <label class="textbox-label">Order Number</label>
                        <input type="number" name="ticket-orderid" value="<?= $ticket->orderId ?>">
                    </div> 
                    <div class="textbox-area">
                        <label class="textbox-label">Status</label>
                        <select name="ticket-status">
                            <option <?= $ticket->status == 1 ? 'selected' : '' ?> value="valid">Valid</option>
                            <option <?= $ticket->status == 2 ? 'selected' : '' ?> value="redeemed">Redeemed</option>
                            <option <?= $ticket->status == 3 ? 'selected' : '' ?> value="cancelled">Cancelled</option>
                            <option <?= $ticket->status == 4 ? 'selected' : '' ?> value="expired">Expired</option>
                            <option <?= $ticket->status == 5 ? 'selected' : '' ?> value="invalid">Invalid</option>
                            <?= $ticket->status == 69 ? '<option selected value="monkey">I\'m a monkey</option>' : '' ?> 
                        </select>
                    </div> 
                    <div class="textbox-area">
                        <label class="textbox-label">Price</label>
                        <input type="number" name="ticket-price" step="0.01" value="<?= $ticket->price ?>">
                    </div>
                    <?php if (isset($_GET['success'])) { ?>
                        <p id='success-text'>Ticket <?= $_GET['ticketuid'] ?> was successfully updated</p>
                        <a href="tickets.php">Close</a>
                    <?php } ?>
                <?php } else { ?>
                    <p class="hint-text">You must first input a valid ticket number before you can continue</p>
                <?php } ?>
            </form>
        </div>
    </body>
</html>
<?php 
    } else {
        echo("You do not have access to view this page");
    }
?>