# Collaborator onboarding

## Source of truth

The tracked ThatBioTutor Hello child theme is the design and behavior source of truth. Hello Elementor is the parent. ACF Free owns approved content fields. Do not reconstruct protected pages in Elementor or add PHP through Code Snippets.

Start with:

```bash
git clone <approved-repository-url>
cd thatbiotutor-wordpress
npm install
npm run wp-env:start
npm run wp-env:setup
scripts/verify-local.sh
```

Theme modules live under `wordpress/wp-content/themes/thatbiotutor-hello-child`. Routes, navigation, fallback copy, and seed provenance live in `inc/reference-content.php`. Templates own section order. CSS/JavaScript own responsive behavior and interactions. Theme assets are local WebP; YouTube is the approved external embed boundary.

Before changing code, preserve existing client ACF values and unrelated WordPress data. Add a focused test, observe it fail, implement, then run the complete verifier. Never insert secrets, live credentials, production exports, or unlicensed captured fonts.

IntensiveFix is separate from regular Sec 3 tuition and must remain an additional page.

