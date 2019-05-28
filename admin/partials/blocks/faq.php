<div class="virgil-pure-global-section">
    <h3 class="virgil-pure-global-page-title">FAQ</h3>
    <p class="virgil-pure-global-faq-question">Do users have to change their passwords if the database has been
        compromised?</p>
    <p class="virgil-pure-global-faq-answer">If a database has been stolen, users do not need to change their original
        passwords. However, you will need to rotate all user records in your database. This will use cryptography to
        disconnect the compromised Pure records from the original passwords, leaving any unauthorized party empty
        handed.</p>
    <hr>
    <p class="virgil-pure-global-faq-question">How does the Recovery Key work?</p>
    <p class="virgil-pure-global-faq-answer">Recovery Key is a key pair that allows you to recover the original user
        password hashes if you ever need to deactivate the Pure plugin. The Recovery Key encrypts the password hashes,
        and stores the encrypted values into the wp_usermeta table in your database.
        <br>
        The Recovery Key utilizes a public and private key pair. The public key is stored in the wp_option table and the
        Private Key must be stored by you securely on an external device.
    </p>
    <hr>
    <p class="virgil-pure-global-faq-question">How much does it cost?</p>
    <p class="virgil-pure-global-faq-answer">Pure is a FREE toolkit. All libraries are open source and can be found on
        GitHub, where they are available for free to any user.</p>
    <hr>
    <p class="virgil-pure-global-faq-question">What if an App Private Key gets lost?</p>
    <p class="virgil-pure-global-faq-answer">There is no way to restore the `APP_SECRET_KEY`. The database records
        become inaccessible and therefore useless. So, it is highly recommended that you immediately create a backup of
        the key in a secure location to avoid losing it.
    </p>
    <hr>
    <p class="virgil-pure-global-faq-answer"><a
                href="https://developer.virgilsecurity.com/docs/use-cases/v1/passwords-and-data-protection?_ga=2.124234441.238283218.1553788809-677779594.1553687510"
                target="_blank">Learn more from our documentation</a></p>
</div>