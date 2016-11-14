# Om at tvinge brugernavnet til at være studienummer

Som inspiration har jeg brugt det link, du sendte, samt et andet:

- [Det du sendte](https://s2member.com/kb-article/using-the-e-mail-address-as-the-username/)
- [Et, der beskrev hvordan man fandt et andet "hook"](https://www.s2member.com/forums/topic/user-registration-without-username/)

Jeg synes, selve koden benytte til formularen på det link, du sendte, var meget kluntet, og jeg kunne ikke umiddelbart se fordelen. Så jeg har skrevet min egen, og bare brugt deres måde at "sætte det ind" i registreringsformularen.

Så lad os starte fra toppen.

## CSS
Til at skjule elementer, er CSS-fremragende. Så lidt CSS skal vi bruge. Men! Hvilke elementer, skal vi skjule? Alt, der har med brugernavn at gøre... Fint -- men hvordan finder vi så det?

Ind på den side, du vil bruge ([link](https://dev.studenterkirurgi.dk/medlemskab/)) og højreklik ca. der, hvor du vil. I de fleste moderne browsere (Safari, Firefox, Chrome -- måske også IE?) kan man så vælge noget i stil med "Inspicér element". Så popper der et vindue/frame op, hvor man kan se de HTML-elementer (DOM-elements), der er på siden -- typisk vil man, når man fører musen over et element også få det highlighted inde på selve siden. Man kan typisk også "folde elementerne ind/ud", så man kan se parent/child elements eller ej. Herigennem fandt jeg så den `<div>`, der indeholdt alt om brugernavn:

```html
<div id="s2member-pro-stripe-checkout-form-username-div" class="s2member-pro-stripe-form-div s2member-pro-stripe-checkout-form-div s2member-pro-stripe-form-username-div s2member-pro-stripe-checkout-form-username-div">
			<label for="s2member-pro-stripe-checkout-username" id="s2member-pro-stripe-checkout-form-username-label" class="s2member-pro-stripe-form-username-label s2member-pro-stripe-checkout-form-username-label">
				<span>Bruger navn (små bogstaver og/eller tal) *</span><br>
				<input type="text" aria-required="true" maxlength="60" autocomplete="off" name="s2member_pro_stripe_checkout[username]" id="s2member-pro-stripe-checkout-username" class="s2member-pro-stripe-username s2member-pro-stripe-checkout-username form-control" value="" tabindex="40" readonly="">
			</label>
		</div>
```

Det, der er mest interessant er, hvis de findes, `id`'er, da de er unikke på en side. Alternativt kan man benytte en `class`, men disse kan deles af flere elementer på samme side, og der er risiko for at ramme nogle utilsigtede elementer.

Så, jeg sletter lige alt, der ikke er nødvendigt:

```html
<div id="s2member-pro-stripe-checkout-form-username-div">
      <label>
				<span>Bruger navn (små bogstaver og/eller tal) *</span><br>
				<input type="text" id="s2member-pro-stripe-checkout-username">
			</label>
    </div>
```

Okay. Så nu kan vi skjule hele boksen ved at lave noget CSS, idet et id får sat et `#` foran, (mens evt. classes skal have `.`) for at blive bestemt:

```css
#s2member-pro-stripe-checkout-form-username-div {
    display: none;
}
```

For god ordens skyld, kan vi også lige smide lidt styles på det felt, der ikke må rettes (hvis vi nu vælger *ikke* at skjule det, `id` er hentet fra HTML'en):


```css
#s2member-pro-stripe-checkout-form-username-div {
    display: none;
}

#s2member-pro-stripe-checkout-username {
	background:#dedede;
	cursor:not-allowed;
}
```

Cool -- så nu er der styr på CSS'en.

## JavaScript
Til at manipulere hjemmesiders HTML er [jQuery](http://jquery.com) fremragende. Koden minder om JavaScript, men gør det meget nemmere at arbejde med elementer på siden, og langt hen ad vejen minder syntaks om css -- man vælger nemlig elementer på samme måde som i CSS.

Så -- vi vil gerne have, at vores brugernavns-felt bliver read-only:

```javascript
// Disabler brugernavn-input-feltet for  illustrative formål,
// i tilfælde af, det er synligt, kan det så ikke rettes.
$("#s2member-pro-stripe-checkout-username").prop("readOnly", true);
```

Done.

Næste punkt er lidt mere bøvlet -- vi vil gerne have, at når folk indtaster deres studienummer, så overføres det til deres brugernavn. Vi kan enten gøre det løbende, mens de taster, eller når de sender formularen. Vi vælger her at gøre det løbende, så brugerne kan følge med. Og hvis vi så skjuler brugernavn-feltet, jamen så er det jo underordnet.

Vi sætter jQuery til at holde øje med, hvornår der bliver tastet noget i input-feltet for studienummer (`id="s2member-pro-stripe-checkout-custom-reg-field-studienummer"`)

```javascript
// Holder øje med indtastning af studienummeret.
// Når der sker noget (dvs. på keyup), kører funktionen indenfor
//  klammerne {}.
$("#s2member-pro-stripe-checkout-custom-reg-field-studienummer").keyup(function() {
	// jQuery-funktionen .val() kan både hente og sætte
	// teksten i input-formularer. Hvis man bare lader
	// parentesen stå tom, hentes den, mens hvis man
	// sætter noget ind i parentesen, sætter man feltets
	// input value til det fx .val("hej")

	// Gemmer studienummeret som variabel, idet det indtastes
	var studienr = $(this).val();

	// Sætter så værdien af det (skjulte?) brugernavn-input-felt til at være = studienr
	$("#s2member-pro-stripe-checkout-username").val(studienr);
})
```

Så -- det er egentlig koden, der klarer arbejdet for os.

Vi har nu gjort vores brugernavn-input skrivebeskyttet (via JS) og stylet det (via CSS), så folk ikke kan bestemme det, og kan *se*, at det ikke er meningen, de skal kunne det.

Næste trin er at koden så kaldes, når formularen vises. Her hentede jeg først inspiration i det link, du sendte. Men det virkede ikke. Det viste sig, at det skyldtes, vi brugte et forkert WordPress `hook`.

Den linje, der i dit link hedder

```php
add_action ("login_head", "s2_customize_login", 1000);
```

hooker sig ind i WordPress via `login_head` og kalder så php-funktionen `s2_customize_login`. Men det er åbenbart ikke den hook, vi skal bruge, når vi kører S2Member i Pro-udgave med Stripe.. Who knows. På det link, jeg selv fandt, får OP svar på sit spørgsmål vedr. authnet(?). Jeg prøvede bare at erstatte authnet med stripe og bum -- det var rigtigt. Så vores linje ender på:

```php
add_action ("ws_plugin__s2member_pro_before_sc_stripe_form", "studienr_as_username", 1000);
```

OK. Så nu kalder vi en funktion, når vi viser formularen. Vores funktion skal så bare indeholde den kode, vi lige har skrevet -- dejlig simpelt. I dit link var der også et tjek for om vi var på "registrerings-siden" (kontra login?). Men det virkede ikke, jeg antager, at det hook, vi benytter, kun bliver brugt, når man skal registrere sig.

Det ser sådan ud:

```php
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
<?php
    }
    add_action ("ws_plugin__s2member_pro_before_sc_stripe_form", "studienr_as_username", 1000);
?>
```

## Final touches
Hvad gør vi, hvis folk ikke har JavaScript slået til? Så kan vi ikke styre deres brugernavn (og betalingen virker vist heller ikke?)?

Vi lader dem ikke registrere sig! Vi smider et `<noscript>...</noscript>` ind, der skjuler hele formularen og kommer med en forklaring:

```html
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
```

## Det endelige resultat

Vi ender altså med at have en fil, jeg har valgt at kalde `studienr-as-username.php`, der ligger som `wp-content/mu-plugins/tudienr-as-username.php`. Den ser sådan her ud:

```php
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
```
