<?php
function studienr_as_username()
    {
?>
        <style>
        #s2member-pro-stripe-checkout-form-username-div {
            /* For at skjule brugernavn-feltet, erstat 'block' med 'none' */
            display: block;
        }

        #s2member-pro-stripe-checkout-username {
        	background:#dedede;
        	cursor:not-allowed;
        }
        </style>

        <script type="text/javascript">
        $(document).ready (function () /* Handles email-to-username on keyup. */
            {

                // Disabler brugernavn-input-feltet for illustrative formål, i tilfælde af, det er synligt, kan det så ikke rettes.
                $("#s2member-pro-stripe-checkout-username").prop("readOnly", true);

                // Holder øje med indtastning af studienummeret. Når der sker noget, kører funktionen
                $("#s2member-pro-stripe-checkout-custom-reg-field-studienummer").keyup(function() {
                    // Gemmer studienummer som variabel, idet det indtastes
                    var studienr = $(this).val();

                    // Sætter så værdien af det (skjulte?) brugernavn-input-felt til at være = studienr
                    $("#s2member-pro-stripe-checkout-username").val(studienr);


                })

        });
        </script>
        <noscript>
            <style>
                #s2member-pro-stripe-checkout-form {
                    display:none;
                }
            </style>
            <div class="alert alert-danger">
                <strong>Ups!</strong> Du er nødt til at slå JavaScript til for at oprette en bruger.
            </div>
        </noscript>
<?php
    }
    add_action ("ws_plugin__s2member_pro_before_sc_stripe_form", "studienr_as_username", 1000);
?>
