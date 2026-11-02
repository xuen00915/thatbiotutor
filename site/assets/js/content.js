(function () {
  "use strict";

  const assetBase = new URL("../images/", document.currentScript.src).href;
  const asset = (file, alt, width, height) =>
    Object.freeze({
      file,
      url: new URL(file, assetBase).href,
      alt,
      width,
      height,
    });

  // CLIENT EDIT: Update image order here. Business details live in site/site.json.
  window.TBT_CONTENT = Object.freeze({
    assetBase,
    assets: Object.freeze({
      // CLIENT EDIT: Homepage hero image order.
      homeHero: Object.freeze([
        asset(
          "home/hero-01.webp",
          "Students conducting a hands-on biology practical in the classroom",
          3024,
          4032
        ),
        asset(
          "home/hero-02.webp",
          "Mr Keefe teaching a biology graph lesson to a full classroom",
          3024,
          2619
        ),
        asset(
          "home/hero-03.webp",
          "ThatBioTutor students gathered together after class",
          2048,
          1536
        ),
        asset(
          "home/hero-04.webp",
          "Students working through a biology lesson in the classroom",
          3024,
          4032
        ),
        asset(
          "home/hero-05.webp",
          "Biology worksheets, stationery, and classroom snacks on a desk",
          1279,
          1843
        ),
      ]),
      trackRecord: Object.freeze([
        asset(
          "shared/track-record-01.webp",
          "A group of ThatBioTutor students smiling in class",
          2048,
          1536
        ),
        asset(
          "shared/track-record-02.webp",
          "Mr Keefe giving focused guidance to students at their desks",
          3024,
          4032
        ),
        asset(
          "shared/track-record-03.webp",
          "Mr Keefe celebrating with three biology students",
          1536,
          2048
        ),
        asset(
          "shared/track-record-04.webp",
          "Mr Keefe leading an online biology class over Zoom",
          1600,
          974
        ),
      ]),
      // CLIENT EDIT: Classroom gallery order.
      homeClassroom: Object.freeze([
        asset(
          "home/classroom-01.webp",
          "Mr Keefe making a biology lesson memorable for students",
          3024,
          4032
        ),
        asset(
          "home/classroom-02.webp",
          "Students learning together in the ThatBioTutor classroom",
          3024,
          4032
        ),
        asset(
          "home/classroom-03.webp",
          "The classroom snack station with drinks and refreshments",
          3024,
          4032
        ),
      ]),
      // CLIENT EDIT: IntensiveFix programme graphic order.
      intensiveFixCarousel: Object.freeze([
        asset(
          "intensivefix/program-01.webp",
          "IntensiveFix holiday bootcamp cover: fix the Sec 3 syllabus in four days",
          1080,
          1350
        ),
        asset(
          "intensivefix/program-02.webp",
          "Who the Sec 3 IntensiveFix programme is designed for",
          1080,
          1350
        ),
        asset(
          "intensivefix/program-03.webp",
          "IntensiveFix lesson structure and Sec 3 Biology topics covered",
          1080,
          1350
        ),
        asset(
          "intensivefix/program-04.webp",
          "IntensiveFix physical and online class options",
          1080,
          1350
        ),
        asset(
          "intensivefix/program-05.webp",
          "FastTrack resources included with the IntensiveFix programme",
          1080,
          1350
        ),
        asset(
          "intensivefix/program-06.webp",
          "Mr Keefe personally teaches IntensiveFix with lesson consultations",
          1080,
          1350
        ),
        asset(
          "intensivefix/program-07.webp",
          "IntensiveFix student feedback about Mr Keefe and the lessons",
          1080,
          1350
        ),
        asset(
          "intensivefix/program-08.webp",
          "IntensiveFix fees, enrolment discount, and referral bonus",
          1080,
          1350
        ),
        asset(
          "intensivefix/program-09.webp",
          "IntensiveFix run one schedule for the last two weeks of November",
          1080,
          1350
        ),
        asset(
          "intensivefix/program-10.webp",
          "IntensiveFix run two schedule for the first two weeks of December",
          1080,
          1350
        ),
      ]),
      intensiveFixReview: Object.freeze([
        asset(
          "intensivefix/student-reviews.webp",
          "IntensiveFix students describe interactive, patient, and clear teaching",
          1080,
          1350
        ),
      ]),
    }),
  });
})();
