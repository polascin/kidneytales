<footer>
  <div class="copyright-container">
    Copyright &copy; <strong><?= date('Y'); ?></strong> <?= isset($t['copyright']) ? $t['copyright'] : 'Ľubomír Polaščín & Kidney Tales Team Contributors. All rights reserved!'; ?>
  </div>
  <div>
    <?= isset($t['app_footer']) ? $t['app_footer'] : 'Kidney Tales at <a href="https://ladvina.eu">ladvina.eu</a> is a multilingual web application designed to help users manage their kidney health and connect with others in the community.'; ?>
  </div>
  <div>
    <a href="/about"><?= isset($t['about']) ? $t['about'] : 'About' ?></a> |
    <a href="/terms"><?= isset($t['terms_of_service']) ? $t['terms_of_service'] : 'Terms of Service' ?></a> |
    <a href="/privacy"><?= isset($t['privacy_policy']) ? $t['privacy_policy'] : 'Privacy Policy' ?></a> |
    <a href="/contact"><?= isset($t['contact']) ? $t['contact'] : 'Contact' ?></a>
  </div>
</footer>

</body>


</html>