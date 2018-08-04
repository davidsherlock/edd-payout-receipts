jQuery(document).ready(function($) {

	$('.edd-payout-receipts-export-toggle').click( function() {
		$('.edd-payout-receipts-export-toggle').toggle();
		$('#edd-payout-receipts-export-payout-receipts').toggle();
	});

	$('body').on('click', '.edd-payout-receipts-download-payout-receipt-file', function(e) {
		$(this).attr('disabled', 'disabled');
		$('#edd-payout-receipts-export-payout-receipts').hide();
		$('#edd-payout-receipts-send-emails').show();
		window.scrollTo(0, 0);
	});

});
