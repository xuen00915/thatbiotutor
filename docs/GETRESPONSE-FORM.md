# Free Bio Notes — GetResponse

The Notes page embeds https://12-dec-2025-updated-signup-form-02264.getresponsesite.com/ directly. The provider owns the fields, subscriber list, validation, email delivery and success behavior. The old Contact Form 7 shortcode and mailto fallback are no longer used.

The supplied form was inspected without submitting: first name, email, four 2027 student-level choices and the parent checkbox are present. No subscriber was created and email delivery was not tested.

## Required success redirect

The client requested a homepage redirect after submission. In GetResponse, change this form’s success action from `https://www.thatbiotutor.com/freenotesthankyou` to the intended homepage (currently `https://xuen00915.github.io/thatbiotutor/`). The supplied published configuration initially used a new-tab thank-you redirect. This must be changed in the provider account; the parent site cannot infer cross-origin form success or safely force a redirect. Confirm this setting before treating the homepage redirect as complete.

The embedded page styling is managed in GetResponse. The separate signup link below the frame was removed at the client’s request. Keep the frame scrollable so validation or confirmation content remains accessible.

## Pending provider appearance changes

Set the Submit background to `#43B43A`, hover to `#3A9F32`, and text to white to match the site buttons. Remove the dark footer strip in the hosted form editor. These elements are inside the cross-origin provider iframe and cannot be styled by the parent website. Provider editor access is required; these changes are not yet applied.
