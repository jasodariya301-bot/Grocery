    <?php
    session_start();
    include('../includes/db_config.php');



    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $_SESSION['offer_message'] = "Offer ID not provided!";
        $_SESSION['offer_message_type'] = "danger";
        header('Location: offers.php');
        exit();
    }

    $offer_id = intval($_GET['id']);

    // First check if offer exists
    $check_query = "SELECT id, offer_title FROM offers WHERE id = $offer_id";
    $check_result = mysqli_query($con, $check_query);

    if (mysqli_num_rows($check_result) == 0) {
        $_SESSION['offer_message'] = "Offer not found!";
        $_SESSION['offer_message_type'] = "danger";
        header('Location: offers.php');
        exit();
    }

    $offer = mysqli_fetch_assoc($check_result);
    $offer_title = $offer['offer_title'];

    // Begin transaction
    mysqli_begin_transaction($con);

    try {
        // First delete product associations (due to foreign key constraint)
        $delete_products = "DELETE FROM offer_products WHERE offer_id = $offer_id";
        if (!mysqli_query($con, $delete_products)) {
            throw new Exception("Failed to delete product associations: " . mysqli_error($con));
        }
        
        // Then delete the offer
        $delete_offer = "DELETE FROM offers WHERE id = $offer_id";
        if (!mysqli_query($con, $delete_offer)) {
            throw new Exception("Failed to delete offer: " . mysqli_error($con));
        }
        
        // Check if any row was actually deleted
        if (mysqli_affected_rows($con) == 0) {
            throw new Exception("No offer was deleted");
        }
        
        // Commit transaction
        mysqli_commit($con);
        
        $_SESSION['offer_message'] = "Offer '$offer_title' deleted successfully!";
        $_SESSION['offer_message_type'] = "success";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);
        
        $_SESSION['offer_message'] = "Error deleting offer: " . $e->getMessage();
        $_SESSION['offer_message_type'] = "danger";
    }

    // Redirect back to offers page
    header('Location: offers.php');
    exit();
    ?>