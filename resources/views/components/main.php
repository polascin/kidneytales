<div class="main-container">

  <div class="header">
    <h1><?= isset($t['welcome_message']) ? $t['welcome_message'] : 'Welcome to Kidney Stories'; ?></h1>
    <p><?= isset($t['app_description']) ? $t['app_description'] : 'A web application for sharing tales and stories from the community of people with kidney disorders, including those on dialysis, and those who have had or are waiting for a kidney transplant.'; ?></p>
  </div>

  <div class="left">
    <menu>
      <nav>
        <ul>
          <li><button><a href="/home"><?= isset($t['home']) ? $t['home'] : 'Home'; ?></a></button></li>
          <li><button><a href="/stories"><?= isset($t['stories']) ? $t['stories'] : 'Stories'; ?></a></button></li>
          <li><button><a href="/about"><?= isset($t['about']) ? $t['about'] : 'About'; ?></a></button></li>
          <li><button><a href="/contact"><?= isset($t['contact']) ? $t['contact'] : 'Contact'; ?></a></button></li>
        </ul>
      </nav>
    </menu>
  </div>

  <div class="main">
    <main>
      <section>
        <article>
          <h1><?= isset($t['welcome_home']) ? $t['welcome_home'] : 'Welcome to Kidney Tales'; ?></h1>
          <p><?= isset($t['home_intro']) ? $t['home_intro'] : 'Welcome to our supportive community for people affected by kidney disorders. Here you can share your story, read others\' experiences, and find support from people who understand your journey.'; ?></p>
        </article>
        <article>
          <p><?= isset($t['home_intro2']) ? $t['home_intro2'] : 'This web application is designed to facilitate the sharing of personal experiences, tales, and stories among individuals affected by kidney disorders, including those undergoing dialysis, those in the pre- or post-dialysis stage, and individuals living without the limitations of dialysis. This platform aims to foster a supportive community, allowing users to connect, share experiences, and provide insights that can help others navigate their journeys with kidney health.'; ?></p>
        </article>
      </section>
    </main>
  </div>

  <div class="right">
    <aside>
      <section>
        <article>
          <h1><?= isset($t['about_kidney_tales']) ? $t['about_kidney_tales'] : 'About Kidney Tales'; ?></h1>
          <p><?= isset($t['kidney_tales_description']) ? $t['kidney_tales_description'] : 'Kidney Tales is a supportive community platform where people affected by kidney disorders can share their experiences, find support, and connect with others on similar journeys.'; ?></p>
        </article>
        <article>
          <h1><?= isset($t['community_guidelines']) ? $t['community_guidelines'] : 'Community Guidelines'; ?></h1>
          <p><?= isset($t['guideline_respectful']) ? $t['guideline_respectful'] : 'Be respectful and supportive to all community members'; ?></p>
          <p><?= isset($t['guideline_privacy']) ? $t['guideline_privacy'] : 'Respect privacy and confidentiality, respect the privacy of others and do not share personal information without consent.'; ?></p>
          <p><?= isset($t['guideline_medical']) ? $t['guideline_medical'] : 'Share experiences, not medical advice. Do not provide medical advice or share personal medical information.'; ?></p>
          <p><?= isset($t['guideline_appropriate']) ? $t['guideline_appropriate'] : 'Keep content appropriate and relevant.'; ?></p>
        </article>
        <article>
          <h1><?= isset($t['support_resources']) ? $t['support_resources'] : 'Support Resources'; ?></h1>
          <p><?= isset($t['support_description']) ? $t['support_description'] : 'If you need immediate medical help or are in crisis, please contact your healthcare provider or emergency services.'; ?></p>
        </article>
      </section>
      <aside>
  </div>

  <div class="footer">
    <h1>Main Content Footer</h1>
  </div>

</div>