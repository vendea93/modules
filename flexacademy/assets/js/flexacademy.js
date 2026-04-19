"use strict";

//instructor cta
$(document).on("click", ".flexacademy-instructor-cta", function () {
  const modal = $("#addInstructorModal");
  const obj = $(this);
  modal.find(".modal-title").text(obj.data("title"));
  modal.find("input[name='instructor_id']").val(obj.data("instructor-id"));
  modal.find("input[name='name']").val(obj.data("instructor-name"));
  modal.find("input[name='email']").val(obj.data("instructor-email"));
  modal.find("input[name='job_title']").val(obj.data("instructor-job-title"));
  modal.find("textarea[name='bio']").val(obj.data("instructor-bio"));
  modal.find("input[type='file']").val("");
  const signatureContainer = modal.find(".flexacademy-current-signature");
  const signatureUrl = obj.data("instructor-signature-url") || "";
  const viewLabel = signatureContainer.data("view-label") || "";
  const prefixLabel = signatureContainer.data("prefix-label") || "";
  if (signatureUrl) {
    signatureContainer.html(
      '<span class="tw-text-xs tw-text-gray-500">' +
        prefixLabel +
        ' <a href="' +
        signatureUrl +
        '" target="_blank" rel="noopener">' +
        viewLabel +
        "</a></span>"
    );
  } else {
    signatureContainer.empty();
  }
  modal.modal("show");
  return false;
});
//delete info field
$(document).on("click", ".flexacademy-delete-field-cta", function () {
  const field = $(this).data("field");
  if (confirm_delete()) {
    $(this).closest("div").remove();
  }
  return false;
});

//add info field
$(document).on("click", ".flexacademy-add-field-cta", function () {
  const field = $(this).data("field");
  const container = $(".flexacademy-" + field + "-container");
  if (field == "faq") {
    const label = $(this).data("label");
    const label2 = $(this).data("label2");
    container.append(
      '<div class="tw-mb-4 flexacademy-' +
        field +
        '-container-each">' +
        '<div class="tw-mb-2"><input type="text" name="question[]" class="form-control tw-mb-2"/> </div' +
        '<div class="tw-mb-2"><textarea name="answer[]"  class="form-control"></textarea>' +
        "</div>" +
        '<a href="javascript:void(0)" class="btn btn-sm btn-secondary flexacademy-delete-field-cta" data-field="' +
        field +
        '"><i class="fa-solid fa-trash"></i></a></div>'
    );
  } else if (field == "requirements") {
    container.append(
      '<div class="tw-mb-4 flexacademy-' +
        field +
        '-container-each"><input type="text" name="' +
        field +
        '[]" class="form-control">' +
        '<a href="javascript:void(0)" class="btn btn-sm btn-secondary flexacademy-delete-field-cta" data-field="' +
        field +
        '"><i class="fa-solid fa-trash"></i></a></div>'
    );
  } else if (field == "outcomes") {
    container.append(
      '<div class="tw-mb-4 flexacademy-' +
        field +
        '-container-each"><input type="text" name="' +
        field +
        '[]"  class="form-control">' +
        '<a href="javascript:void(0)" class="btn btn-sm btn-secondary flexacademy-delete-field-cta" data-field="' +
        field +
        '"><i class="fa-solid fa-trash"></i></a></div>'
    );
  }
  return false;
});
//delete question
$(document).on(
  "click",
  ".flexacademy-quiz-questions-delete-question",
  function () {
    const obj = $(this);
    const msg = obj.data("msg");
    if (!confirm(msg)) {
      return false;
    }
    const question_id = $(this).data("question-id");
    const url = $("#flexacademy_ajax_url").val();
    const data = {
      action: "delete_quiz_question",
      question_id: question_id,
    };
    $.post(url, data, function (response) {
      const r = JSON.parse(response);
      if (r.status == "success") {
        //remove the question from the list
        obj.closest("tr").remove();
        alert_float("success", r.message);
      } else {
        alert_float("error", r.message);
      }
    });
  }
);
//add/edit question button
$(document).on("click", ".flexacademy-quiz-questions-cta", function () {
  const question_id = $(this).data("question-id");
  const question_type = $(this).data("question-type");
  const question = $(this).data("question");
  const correct_answer = $(this).data("correct-answer");
  const options = $(this).data("options");
  const container = $(".flexacademy-quiz-questions-add-question-form");
  container.find("input[name='question_id']").val(question_id);
  container.find("select[name='question_type']").val(question_type);
  container.find("select[name='question_type']").trigger("change");
  //tiny mce set content
  tinymce.get("question").setContent(question);
  if (question_type == "true-false") {
    //convert the correct answer to true or false string
    container
      .find("select[name='correct_answer_true_false']")
      .val(correct_answer);
    container
      .find("select[name='correct_answer_true_false']")
      .trigger("change");
  } else if (question_type == "single" || question_type == "multiple") {
    //tags input set value
    const optionsInput = container.find("input[name='options[]']");
    const correctAnswerInput = container.find(
      "input[name='correct_answer_choice[]']"
    );

    // Clear existing tags first by removing all current tags
    optionsInput.tagit("assignedTags").forEach(function (tag) {
      optionsInput.tagit("removeTagByLabel", tag);
    });
    correctAnswerInput.tagit("assignedTags").forEach(function (tag) {
      correctAnswerInput.tagit("removeTagByLabel", tag);
    });

    // Set new values using createTag method
    if (options && options.length > 0) {
      //if it has comma, then split it and create tags
      if (options.includes(",")) {
        const optionsArray = options.split(",");
        optionsArray.forEach(function (option) {
          optionsInput.tagit("createTag", option.trim());
        });
      } else {
        optionsInput.tagit("createTag", options);
      }
    }

    if (correct_answer && correct_answer.length > 0) {
      if (correct_answer.includes(",")) {
        const correctAnswerArray = correct_answer.split(",");
        correctAnswerArray.forEach(function (answer) {
          correctAnswerInput.tagit("createTag", answer.trim());
        });
      } else {
        correctAnswerInput.tagit("createTag", correct_answer);
      }
    }
    //
  } else if (question_type == "fill-in-the-blank") {
    container.find("input[name='correct_answer']").val(correct_answer);
  }
  if ($(this).data("action") == "add") {
    container.toggle();
  } else {
    container.show();
  }

  return false;
});
//question type
$(document).on(
  "change",
  ".flexacademy-quiz-questions-add-question-form select[name='question_type']",
  function () {
    const question_type = $(this).val();
    if (question_type == "true-false") {
      $(".flexacademy-quiz-true-false-container").show();
      $(".flexacademy-quiz-choice-container").hide();
      $(".flexacademy-quiz-fill-in-the-blank-container").hide();
    }
    if (question_type == "fill-in-the-blank") {
      $(".flexacademy-quiz-fill-in-the-blank-container").show();
      $(".flexacademy-quiz-true-false-container").hide();
      $(".flexacademy-quiz-choice-container").hide();
    }
    if (question_type == "single" || question_type == "multiple") {
      $(".flexacademy-quiz-choice-container").show();
      $(".flexacademy-quiz-true-false-container").hide();
      $(".flexacademy-quiz-fill-in-the-blank-container").hide();
    }
  }
);
//click on quiz questions to show Modal
$(document).on("click", ".flexacademy-quiz-questions", function () {
  const quiz_id = $(this).data("quiz-id");
  const modal = $("#flexacademy-quiz-questions-modal");
  modal.find("input[name='quiz_id']").val(quiz_id);
  //get the questions view and set it to the modal
  const url = $("#flexacademy_ajax_url").val();
  const data = {
    action: "get_quiz_questions",
    quiz_id: quiz_id,
  };
  $.post(url, data, function (response) {
    const r = JSON.parse(response);
    if (r.status == "success") {
      modal.find(".flexacademy-quiz-questions-list").html(r.html);
    } else {
      alert_float("error", r.message);
    }
  });
  modal.modal("show");
  return false;
});

//click on quiz results
$(document).on("click", ".flexacademy-quiz-results", function () {
  const quiz_id = $(this).data("quiz-id");
  const url = $("#flexacademy_ajax_url").val();
  const data = {
    action: "get_quiz_results",
    quiz_id: quiz_id,
  };
  $.post(url, data, function (response) {
    const r = JSON.parse(response);
    if (r.status == "success") {
      alert_float("success", r.message);
      $("#flexacademy-quiz-results-modal").html(r.html);
      $("#flexacademy-quiz-results-modal").modal("show");
    } else {
      alert_float("error", r.message);
    }
  });
});

//quiz add and edit functionality
$(document).on("click", ".flexacademy-quiz-cta", function () {
  const course_id = $(this).data("course-id");
  const quiz_id = $(this).data("quiz-id");
  const quiz_label = $(this).data("quiz-label");
  const quiz_total_marks = $(this).data("quiz-total-marks");
  const quiz_pass_marks = $(this).data("quiz-pass-marks");
  const quiz_retake_limit = $(this).data("quiz-retake-limit");
  const quiz_time_limit = $(this).data("quiz-time-limit");
  const quiz_description = $(this).data("quiz-description");
  const quiz_section_id = $(this).data("section-id");
  const quiz_title = $(this).data("quiz-title");
  const modal = $("#addQuizModal");
  modal.find(".modal-title").text(quiz_label);
  modal.find("input[name='course_id']").val(course_id);
  modal.find("input[name='quiz_id']").val(quiz_id);
  modal.find("input[name='title']").val(quiz_title);
  modal.find("input[name='total_marks']").val(quiz_total_marks);
  modal.find("input[name='pass_marks']").val(quiz_pass_marks);
  modal.find("input[name='retake_limit']").val(quiz_retake_limit);
  modal.find("input[name='time_limit']").val(quiz_time_limit);
  tinymce.get("description").setContent(quiz_description);
  modal.find("select[name='section_id']").val(quiz_section_id);
  modal.find("select[name='section_id']").trigger("change");
  modal.modal("show");
  return false;
});
//get lessons by section
$(document).on("change", "#flexacademy-lesson-section-order-list", function () {
  const section_id = $(this).val();
  const url = $("#flexacademy_ajax_url").val();
  const data = {
    action: "get_lessons_by_section",
    section_id: section_id,
  };
  $.post(url, data, function (response) {
    const r = JSON.parse(response);
    if (r.status == "success") {
      $(".flexacademy-lesson-order-list-container").html(r.html);
      flexacademy_initialize_sortable_action("#flexacademy-lesson-order-list");
    } else {
      alert_float("error", r.message);
    }
  });
});
// Lesson add and edit functionality
$(document).on("click", ".flexacademy-lesson-cta", function () {
  const course_id = $(this).data("course-id");
  const lesson_id = $(this).data("lesson-id");
  const lesson_label = $(this).data("lesson-label");
  const lesson_title = $(this).data("lesson-title");
  const section_id = $(this).data("section-id");
  const lesson_duration = $(this).data("duration");
  const lesson_file_path = $(this).data("file-path"); //this is a file path, not needed here
  const lesson_lesson_type = $(this).data("lesson-type");
  const lesson_text_lesson = $(this).data("text-lesson");
  const lesson_summary = $(this).data("summary");
  const lesson_file_url = $(this).data("file-url");
  const lesson_file_source = $(this).data("file-source");
  const modal = $("#addLessonModal");
  modal.find(".modal-title").text(lesson_label);
  modal.find("input[name='course_id']").val(course_id);
  modal.find("input[name='lesson_id']").val(lesson_id);
  modal.find("input[name='title']").val(lesson_title);
  modal.find("select[name='section_id']").val(section_id); //selected
  modal.find("select[name='section_id']").trigger("change"); //trigger change event
  modal.find("input[name='duration']").val(lesson_duration);
  modal.find("select[name='lesson_type']").val(lesson_lesson_type);
  modal.find("select[name='lesson_type']").trigger("change"); //trigger change event
  modal.find("select[name='file_source']").val(lesson_file_source);
  modal.find("select[name='file_source']").trigger("change"); //trigger change event
  tinymce.get("text_lesson").setContent(lesson_text_lesson);
  //file_url_input
  modal.find("input[name='file_url']").val(lesson_file_url);
  tinymce.get("summary").setContent(lesson_summary);

  modal.modal("show");
  return false;
});
//lesson type

//file_source
$(document).on("change", "#file_source", function () {
  const file_source = $(this).val();
  if (file_source == "upload-file") {
    $(".flexacademy-file-upload-container").show();
    $(".flexacademy-file-url-container").hide();
    $(".flexacademy-file-url-container input").prop("disabled", true);
  } else {
    $(".flexacademy-file-upload-container").hide();
    $(".flexacademy-file-url-container").show();
    $(".flexacademy-file-url-container input").prop("disabled", false);
  }
});
//lesson_type
$(document).on("change", "#lesson_type", function () {
  const lesson_type = $(this).val();
  if (lesson_type == "file") {
    $(".flexacademy-file-source-container").show();
    $(".flexacademy-file-source-container select").prop("disabled", false);
    $(".flexacademy-text-container").hide();
    $(".flexacademy-text-container textarea").prop("disabled", true);
  } else {
    $(".flexacademy-file-source-container").hide();
    $(".flexacademy-file-source-container select").prop("disabled", true);
    $(".flexacademy-text-container").show();
    $(".flexacademy-text-container textarea").prop("disabled", false);
  }
});
//create and edit sections
$(document).on("click", ".flexacademy-section-cta", function () {
  const course_id = $(this).data("course-id");
  const section_id = $(this).data("section-id");
  const section_label = $(this).data("section-label");
  const section_title = $(this).data("section-title");
  const modal = $("#addSectionModal");
  modal.find(".modal-title").text(section_label);
  modal.find("#course_id").val(course_id);
  modal.find("#section_title").val(section_title);
  modal.find("input[name='section_id']").val(section_id);
  modal.modal("show");
  return false;
});
//create and edit categories
$(document).on("click", ".flexacademy-create-edit-category", function () {
  var modal = $("#flexacademy-create-edit-category-modal");
  const obj = $(this);
  modal.find(".modal-title").text(obj.data("title"));
  modal.find(".btn-primary").text(obj.data("button-text"));
  modal.find("#category_id").val(obj.data("id"));
  modal.find('input[name="title"]').val(obj.data("name"));
  modal.find('textarea[name="description"]').val(obj.data("description"));
  modal
    .find("#parent_id option[value='" + obj.data("parent-id") + "']")
    .prop("selected", true);
  const status =
    obj.data("status") == 1
      ? modal.find("#y_opt_1_status").prop("checked", true)
      : modal.find("#y_opt_2_status").prop("checked", true);

  modal.modal("show");
  return false;
});

//order and update blocks arrangment
function flexacademy_initialize_sortable_action(container_id) {
  const container = container_id
    ? $(container_id)
    : $("#flexacademy-order-list");
  const type = container.data("type");
  if ($(container).length) {
    $(container).sortable({
      placeholder: "ui-state-highlight-flexacademy",
      update: function (event, ui) {
        flexacademy_update_actions_order(type, container_id);
      },
    });
  }
}

function flexacademy_update_actions_order(type, container_id) {
  const lists = [];
  $(container_id + " .flexacademy-order-item").each(function () {
    lists.push($(this).data("id"));
  });
  const url = $("#flexacademy_ajax_url").val();
  const data = {
    action: "update_actions_order",
    lists: lists,
    type: type,
  };
  $.post(url, data, function (response) {
    const r = JSON.parse(response);
    if (r.status == "success") {
      alert_float("success", r.message);
    } else {
      alert_float("error", r.message);
    }
  });
}

//pricing_type
$(document).on("change", "#pricing_type", function () {
  const pricing_type = $(this).val();
  if (pricing_type == "paid") {
    $(".flexacademy-pricing-type-paid").show();
    $(".flexacademy-pricing-type-paid input").prop("disabled", false);
  } else {
    $(".flexacademy-pricing-type-paid").hide();
    $(".flexacademy-pricing-type-paid input").prop("disabled", true);
  }
});

//expiry_type

$(document).on("change", "#expiry_type", function () {
  const expiry_type = $(this).val();
  if (expiry_type == "never") {
    $(".flexacademy-expiry-type-value").hide();
    $(".flexacademy-expiry-type-value input").prop("disabled", true);
  } else {
    $(".flexacademy-expiry-type-value").show();
    $(".flexacademy-expiry-type-value input").prop("disabled", false);
  }
});

// course_details_tab
$("#flexacademy-tabs > button[data-tab]").each(function () {
  $(this).on("click", () => {
    const attribute = $(this).attr("data-tab");
    $(this)
      .addClass("flexacademy-tabs-active")
      .siblings()
      .removeClass("flexacademy-tabs-active");

    $(`#${attribute}`)
      .addClass("flexacademy-tabs-pane-active")
      .removeClass("flexacademy-tabs-pane")
      .siblings()
      .addClass("flexacademy-tabs-pane")
      .removeClass("flexacademy-tabs-pane-active");
  });
});

$(".flexacademy-instructor-avatar").on("click", () => {
  $(".flexacademy-tabs > button[data-tab='instructors']")
    .addClass("flexacademy-tabs-active")
    .siblings()
    .removeClass("flexacademy-tabs-active");

  $("#instructors")
    .addClass("flexacademy-tabs-pane-active")
    .removeClass("flexacademy-tabs-pane")
    .siblings()
    .addClass("flexacademy-tabs-pane")
    .removeClass("flexacademy-tabs-pane-active");
});

// course_details_faq
$(" #flexacademy-faq > button[data-faq]").each(function () {
  $(this).on("click", () => {
    const attribute = $(this).attr("data-faq");
    $(this)
      .toggleClass("flexacademy-faq-active")
      .siblings()
      .parent()
      .siblings()
      .children("button[data-faq]")
      .removeClass("flexacademy-faq-active")
      .siblings()
      .addClass("flexacademy-faq-content");

    $(`#${attribute}`).toggleClass("flexacademy-faq-content");
  });
});

// Remove from cart functionality
$(document).on("click", "#flexacademy-remove-from-cart-btn", function (e) {
  e.preventDefault();

  const courseId = $(this).data("course-id");
  const confirmMessage = $(this).data("confirm-message");
  const btn = $(this);
  
  // Get language strings from hidden inputs
  const error_message = $("#flexacademy-error").val();
  const course_removed_message = $("#flexacademy-course-removed").val();
  const course_removed_error_message = $("#flexacademy-failed-remove").val();

  if (confirm(confirmMessage)) {
    btn.prop("disabled", true);

    $.ajax({
      url: site_url + "flexacademy/ajax",
      type: "POST",
      data: {
        action: "remove_from_cart",
        course_id: courseId,
      },
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          // Update cart count in header
          if (response.cart_count !== undefined) {
            const cartBadge = $(".flexacademy-cart-count");
            cartBadge.text(response.cart_count);
            if (response.cart_count === 0) {
              cartBadge.addClass("tw-hidden");
            }
          }

          alert_float("success", response.message || course_removed_message);

          // Reload page to update cart
          setTimeout(function () {
            location.reload();
          }, 500);
        } else {
          alert_float("error", response.message || course_removed_error_message);
          btn.prop("disabled", false);
        }
      },
      error: function (xhr, status, error) {
        
        alert_float("error", error_message);
        btn.prop("disabled", false);
      },
    });
  }
});

// Add to cart functionality
$(document).on("click", "#flexacademy-add-to-cart-btn", function (e) {
  e.preventDefault();
  const btn = $(this);
  const courseId = btn.data("course-id");
  
  // Get language strings from hidden inputs
  const error_message = $("#flexacademy-error").val();
  const langCourseIdMissing = $("#flexacademy-course-id-missing").val();
  const langCourseAdded = $("#flexacademy-course-added").val();
  const langFailedAdd = $("#flexacademy-failed-add").val();
  
  if (!courseId) {
    alert_float('danger', langCourseIdMissing);
    return;
  }
  
  // If button is already disabled (item in cart), redirect to cart
  if (btn.prop("disabled")) {
    window.location.href = site_url + "flexacademy/cart";
    return;
  }
  
  // Disable button during request
  btn.prop("disabled", true);
  
  $.ajax({
    url: site_url + "flexacademy/ajax",
    type: "POST",
    data: {
      action: "add_to_cart",
      course_id: courseId
    },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        // Update cart count in header
        if (response.cart_count !== undefined) {
          const cartBadge = $(".flexacademy-cart-count");
          cartBadge.text(response.cart_count);
          if (response.cart_count > 0) {
            cartBadge.removeClass("tw-hidden");
          }
        }
        
        alert_float("success", response.message || langCourseAdded);
        
        // Redirect to cart page after a short delay
        setTimeout(function() {
          window.location.href = site_url + "flexacademy/cart";
        }, 500);
      } else {
        // If course already in cart, redirect to cart page
        if (response.already_in_cart) {
          // Update cart count if available
          if (response.cart_count !== undefined) {
            const cartBadge = $(".flexacademy-cart-count");
            cartBadge.text(response.cart_count);
            if (response.cart_count > 0) {
              cartBadge.removeClass("tw-hidden");
            }
          }
          
          alert_float("info", response.message);
          setTimeout(function() {
            window.location.href = site_url + "flexacademy/cart";
          }, 500);
        } else {
          alert_float("error", response.message || langFailedAdd);
          btn.prop("disabled", false);
        }
      }
    },
    error: function (xhr, status, error) {
      alert_float("error", error_message);
      btn.prop("disabled", false);
    }
  });
});

function initFlexacademyTagsInput() {
  if ($("input.flexacademy-tagsinput").length > 0) {
  $("input.flexacademy-tagsinput").tagit({
    allowSpaces: true,
    animate: false,
  });
}}

// Accordion functionality
$(document).on("click", "#flexacademy-accordion-header", function () {
  const targetId = $(this).data("accordion");
  const content = $("#" + targetId);
  const icon = $(this).find(".flexacademy-accordion-icon");
  
  // Toggle content visibility
  if (content.hasClass("show")) {
    content.removeClass("show").addClass("tw-hidden");
    icon.removeClass("rotated");
  } else {
    content.removeClass("tw-hidden").addClass("show");
    icon.addClass("rotated");
  }
});

// Courses Listing Page
$(document).on("change", '.flexacademy-filter-category, .flexacademy-filter-pricing', function() {
  flexacademy_apply_course_filters();
});

$(document).on("input", "#flexacademy-course-search", function() {
  clearTimeout(window.flexacademySearchTimeout);
  window.flexacademySearchTimeout = setTimeout(function() {
    flexacademy_apply_course_filters();
  }, 500);
});

$(document).on("change", "#flexacademy-goto-page", function() {
  const page = $(this).val();
  const maxPage = $(this).attr("max");
  if (page >= 1 && page <= maxPage) {
    flexacademy_apply_course_filters(page);
  }
});

$(document).on("click", "#flexacademy-grid-view-btn", function() {
  $(this).addClass("active");
  $("#flexacademy-list-view-btn").removeClass("active");
  $("#flexacademy-courses-grid").removeClass("flexacademy-courses-list").addClass("flexacademy-courses-grid");
});

$(document).on("click", "#flexacademy-list-view-btn", function() {
  $(this).addClass("active");
  $("#flexacademy-grid-view-btn").removeClass("active");
  $("#flexacademy-courses-grid").removeClass("flexacademy-courses-grid").addClass("flexacademy-courses-list");
});

function flexacademy_apply_course_filters(page = 1) {
  const category = $('.flexacademy-filter-category:checked').val();
  const pricing = $('.flexacademy-filter-pricing:checked').val();
  const search = $("#flexacademy-course-search").val();

  let url = "?page=" + page;
  if (category) url += "&category=" + category;
  if (pricing) url += "&pricing=" + pricing;
  if (search) url += "&search=" + encodeURIComponent(search);

  window.location.href = url;
}

function flexacademy_apply_mobile_course_filters(page = 1) {
  const category = $('.flexacademy-mobile-filter-category:checked').val();
  const pricing = $('.flexacademy-mobile-filter-pricing:checked').val();
  const search = $("#flexacademy-mobile-course-search").val();

  let url = "?page=" + page;
  if (category) url += "&category=" + category;
  if (pricing) url += "&pricing=" + pricing;
  if (search) url += "&search=" + encodeURIComponent(search);

  window.location.href = url;
}

// Mobile filter inputs
$(document).on("change", ".flexacademy-mobile-filter-category, .flexacademy-mobile-filter-pricing", function() {
  flexacademy_apply_mobile_course_filters();
});

$(document).on("input", "#flexacademy-mobile-course-search", function() {
  clearTimeout(window.flexacademyMobileSearchTimeout);
  window.flexacademyMobileSearchTimeout = setTimeout(function() {
    flexacademy_apply_mobile_course_filters();
  }, 500);
});

// Enrollment functionality
$(document).on("click", ".flexacademy-enroll-btn", function() {
  const courseId = $(this).data("course-id");
  const button = $(this);
  
  if (!courseId) {
    alert_float('danger', button.data('error-course-id-missing')); 
    return;
  }
  
  button.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> ' + button.data('enrolling-text') + '...');
  
  $.ajax({
    url: site_url + "flexacademy/ajax",
    type: "POST",
    data: {
      action: "enroll_course",
      course_id: courseId
    },
    dataType: "json",
    success: function(response) {
      if (response.status === "success") {
        if (response.redirect) {
          window.location.href = response.redirect;
        } else {
          alert_float('success', response.message);
          location.reload();
        }
      } else {
        if (response.redirect) {
          window.location.href = response.redirect;
        } else {
          alert_float('danger', response.message);
        }
      }
    },
    error: function() {
      alert_float('danger', button.data('error-try-again'));
    },
    complete: function() {
      button.prop("disabled", false).html('<i class="fa fa-graduation-cap"></i> ' + button.data('enroll-text'));
    }
  });
});

// Checkout button - creates invoice and redirects to payment
$(document).on("click", "#flexacademy-checkout-btn", function(e) {
  e.preventDefault();
  
  const button = $(this);
  const originalText = button.html();
  
  // Disable button and show loading state
  button.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> ' + button.data('processing-text'));
  
  $.ajax({
    url: site_url + "flexacademy/ajax",
    type: "POST",
    data: {
      action: "checkout"
    },
    dataType: "json",
    success: function(response) {
      if (response.status === "success") {
        // Show success message then redirect to invoice
        alert_float('success', response.message);
        setTimeout(function() {
          window.location.href = response.redirect;
        }, 1000);
      } else {
        let errorMsg = response.message || button.data('error-try-again');
        
        // Add helpful details to error message
        if (response.already_enrolled && response.already_enrolled.length > 0) {
          errorMsg += "\n\nAlready enrolled in: " + response.already_enrolled.join(", ");
        }
        if (response.course_not_found && response.course_not_found.length > 0) {
          errorMsg += "\n\nCourses not found: " + response.course_not_found.join(", ");
        }
        
        alert_float('danger', errorMsg);
        button.prop("disabled", false).html(originalText);
      }
    },
    error: function(xhr, status, error) {
      alert_float('danger', button.data('error-try-again'));
      button.prop("disabled", false).html(originalText);
    }
  });
});

// Unenrollment removed – feature disabled

// Lesson progress functionality
function updateLessonProgress(status, button, timeSpent = 0, score = null) {
  const lessonId = button.data("lesson-id");
  
  if (!lessonId) {
    return;
  }
  
  $.ajax({
    url: site_url + "flexacademy/ajax",
    type: "POST",
    data: {
      action: "update_lesson_progress",
      lesson_id: lessonId,
      status: status,
      time_spent: timeSpent,
      score: score
    },
    dataType: "json",
    success: function(response) {
      if (response.status === "success") {
        // Update UI to reflect new status
        location.reload();
      } else {
        alert_float('danger', response.message);
      }
    },
    error: function() {
      alert_float('danger', button.data('error-try-again'));
    }
  });
}

$(document).on("click", ".flexacademy-mark-complete-btn", function() {
  const button = $(this);
  
  button.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> ' + button.data('updating-text') + '...');
  
  updateLessonProgress('completed', button);
});

$(document).on("click", ".flexacademy-mark-incomplete-btn", function() {
  const button = $(this);
  
  button.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> ' + button.data('updating-text') + '...');
  
  updateLessonProgress('not_started', button);
});

$(function () {
  flexacademy_initialize_sortable_action();
  flexacademy_initialize_sortable_action(
    "#flexacademy-order-section-modal .flexacademy-order-list"
  );
  initFlexacademyTagsInput();
});

// Player tabs functionality (using IDs)
$(document).on("click", "#summary-tab, #certificate-tab", function() {
  const tab = $(this).data("tab");
  
  $("#summary-tab, #certificate-tab").removeClass("flexacademy-tab-active");
  $(this).addClass("flexacademy-tab-active");
  
  $(".flexacademy-tab-pane").removeClass("flexacademy-tab-pane-active");
  $("#" + tab + "-pane").addClass("flexacademy-tab-pane-active");
});

$(document).on("click", ".flexacademy-certificate-download-button", function (e) {
  e.preventDefault(); 
  const certificateTarget = $(this).data("certificate-target").slice(1);
  const certificatePrefix = $(this).data("certificate-prefix");
  const successMessage = $(this).data("success-message");
  const errorMessage = $(this).data("error-message");
  //const modalContent = document.getElementById(certificateTarget);
  const modalContent = $('#' + certificateTarget + ' .flexacademy-certificate-frame').html();

  try{  
    $(this).prop('disabled', true);
      const opt = {
          margin:       [10, 10, 20, 10], // [top, left, bottom, right]
          filename:     certificatePrefix + '.pdf',
          html2canvas:  { scale: 1 },      // Improve quality
          jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };

      html2pdf()
          .from(modalContent)
          .set(opt)
          .save();
    
      $(this).prop('disabled', false);
      alert_float('success', successMessage);
  }catch (e){
    alert_float('danger', errorMessage);
    $(this).prop('disabled', false);
  }
});

// Section collapse/expand functionality
$(document).on("click", ".flexacademy-section-header", function(e) {
  e.preventDefault();
  const section = $(this).closest(".flexacademy-curriculum-section");
  const icon = $(this).find(".flexacademy-section-icon");
  const lessons = section.find(".flexacademy-section-lessons");
  
  section.toggleClass("flexacademy-section-collapsed");
  lessons.slideToggle(200);
  
  if (section.hasClass("flexacademy-section-collapsed")) {
    icon.removeClass("fa-chevron-down").addClass("fa-chevron-right");
  } else {
    icon.removeClass("fa-chevron-right").addClass("fa-chevron-down");
  }
});

// Lesson checkbox - mark as complete/incomplete
$(document).on("change", ".flexacademy-lesson-checkbox", function(e) {
  e.stopPropagation();
  
  const checkbox = $(this);
  const lessonId = checkbox.data("lesson-id");
  const isChecked = checkbox.is(":checked");
  
  if (!lessonId) {
    return;
  }
  
  // Disable checkbox while processing
  checkbox.prop("disabled", true);
  
  $.ajax({
    url: site_url + "flexacademy/ajax",
    type: "POST",
    data: {
      action: "update_lesson_progress",
      lesson_id: lessonId,
      status: isChecked ? "completed" : "not_started",
      time_spent: 0,
      score: null
    },
    dataType: "json",
    success: function(response) {
      if (response.status === "success") {
        // Update UI without reload - just update the progress bar
        location.reload();
      } else {
        // Revert checkbox on error
        checkbox.prop("checked", !isChecked);
        alert_float('danger', response.message);
      }
    },
    error: function() {
      // Revert checkbox on error
      checkbox.prop("checked", !isChecked);
    },
    complete: function() {
      checkbox.prop("disabled", false);
    }
  });
});

// Video progress tracking for lesson page
document.addEventListener("DOMContentLoaded", function() {
    const video = document.getElementById("flexacademy-lesson-video");
    if (video) {
        let timeUpdateInterval;
        
        video.addEventListener("play", function() {
            timeUpdateInterval = setInterval(function() {
                updateLessonProgress("in_progress", Math.floor(video.currentTime * 60)); // Convert to minutes
            }, 30000); // Update every 30 seconds
        });
        
        video.addEventListener("pause", function() {
            if (timeUpdateInterval) {
                clearInterval(timeUpdateInterval);
            }
        });
        
        video.addEventListener("ended", function() {
            if (timeUpdateInterval) {
                clearInterval(timeUpdateInterval);
            }
            updateLessonProgress("completed", Math.floor(video.duration * 60)); // Convert to minutes
        });
    }
});

// Staff Training: Course Enrollment (Free and Paid)
$(document).on('click', '.flexacademy-enroll-free, .flexacademy-enroll-staff', function(e) {
    e.preventDefault();
    
    const $btn = $(this);
    const courseId = $btn.data('course-id');
    const originalHtml = $btn.html();
    
    // Disable button and show loading state
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: admin_url + 'flexacademy/ajax',
        type: 'POST',
        data: {
            action: 'enroll_staff',
            course_id: courseId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                alert_float('success', response.message);
                // Reload page after 1 second to show updated enrollment status
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            } else {
                alert_float('danger', response.message);
                $btn.prop('disabled', false).html(originalHtml);
            }
        },
        error: function() {
            $btn.prop('disabled', false).html(originalHtml);
        }
    });
});

$(document).on('click', '#start-quiz-btn', function() {
    const button = $(this);
    const quizId = $(this).data('quiz-id');
    const enrollmentId = $(this).data('enrollment-id');
    
    $.ajax({
        url: site_url + 'flexacademy/ajax',
        type: 'POST',
        data: {
            action: 'start_quiz_attempt',
            quiz_id: quizId,
            enrollment_id: enrollmentId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                location.reload();
            } else {
                alert_float('danger', response.message);
            }
        },
        error: function() {
            alert_float('danger', button.data('error-start-quiz'));
        }
    });
});

$(document).on('submit', '#quiz-form', function(e) {
    e.preventDefault();

    const $form = $(this);
    const isAutoSubmit = Boolean($form.data('auto-submit'));
    
    $form.data('auto-submit', false);
    
    const formData = $form.serializeArray();
    const answers = {};
    
    formData.forEach(item => {
        if (item.name.startsWith('answer_')) {
            const questionId = item.name.replace('answer_', '');
            answers[questionId] = item.value;
        }
    });
    
    const submitBtnText = $form.data('submit-text');
    
    $.ajax({
        url: site_url + 'flexacademy/ajax',
        type: 'POST',
        data: {
            action: 'submit_quiz',
            attempt_id: $form.data('attempt-id'),
            quiz_id: $form.data('quiz-id'),
            answers: JSON.stringify(answers)
        },
        dataType: 'json',
        beforeSend: function() {
            $form.find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ');
        },
        success: function(response) {
            if (response.status === 'success') {
                alert_float('success', response.message + ' - Score: ' + response.score + '%');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                alert_float('danger', response.message);
                $form.find('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-check"></i> ' + submitBtnText);
            }
        },
        error: function() {
            $form.find('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-check"></i> ' + submitBtnText);
        }
    });
});

function initQuizTimer() {
    const $quizForm = $('#quiz-form');
    if (!$quizForm.length) return;
    
    const startTimeStr = $quizForm.data('start-time');
    const timeLimit = parseInt($quizForm.data('time-limit'), 10);
    const timeLimitMsg = $quizForm.data('time-limit-message') || 'Time is up!';
    const $timerDisplay = $('#quiz-timer-display');
    
    if (!startTimeStr || !timeLimit || isNaN(timeLimit) || timeLimit <= 0) return;
    
    const startTime = new Date(startTimeStr.replace(' ', 'T') + 'Z').getTime();
    let timeExpired = false;
    let timerInterval = null;
    
    function updateTimerDisplay() {
        if (timeExpired) return;
        
        const now = Date.now();
        const elapsed = Math.floor((now - startTime) / 1000);
        const remaining = Math.max(0, timeLimit - elapsed);
        
        if ($timerDisplay.length) {
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            const timeString = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            $timerDisplay.text(timeString);
            
            const $timerContainer = $timerDisplay.closest('.flexacademy-quiz-timer');
            if (remaining <= 60) {
                $timerContainer.addClass('text-danger');
            } else if (remaining <= 300) {
                $timerContainer.addClass('text-warning');
            }
        }
        
        if (remaining <= 0 && !timeExpired) {
            timeExpired = true;
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            alert_float('danger', timeLimitMsg);
            if ($quizForm.length) {
                $quizForm.data('auto-submit', true);
                $quizForm.trigger('submit');
            }
        }
    }
    
    if (isNaN(startTime) || startTime <= 0) {
        return;
    }
    
    const initialNow = Date.now();
    const initialElapsed = Math.floor((initialNow - startTime) / 1000);
    const initialRemaining = timeLimit - initialElapsed;
    
    if (initialRemaining <= 5) {
        if ($timerDisplay.length) {
            $timerDisplay.text('00:00');
            $timerDisplay.closest('.flexacademy-quiz-timer').addClass('text-danger');
        }
        alert_float("danger", timeLimitMsg);
        $quizForm.data('auto-submit', true);
        $quizForm.trigger('submit');
        return;
    }
    
    updateTimerDisplay();
    timerInterval = setInterval(updateTimerDisplay, 1000);
}

let currentQuestionIndex = 0;

function updateQuizNavigation() {
    const $slides = $('.flexacademy-quiz-question-slide');
    const totalQuestions = $slides.length;
    
    if (totalQuestions === 0) return;
    
    $slides.removeClass('active');
    $slides.eq(currentQuestionIndex).addClass('active');
    
    $('#quiz-back-btn').prop('disabled', currentQuestionIndex === 0);
    $('#quiz-prev-side').prop('disabled', currentQuestionIndex === 0);
    
    if (currentQuestionIndex === totalQuestions - 1) {
        $('#quiz-next-btn').hide();
        $('#quiz-next-side').hide();
        $('#quiz-submit-btn').show();
    } else {
        $('#quiz-next-btn').show();
        $('#quiz-next-side').show();
        $('#quiz-submit-btn').hide();
        $('#quiz-next-side').prop('disabled', false);
    }
}

$(document).ready(function() {
    initQuizTimer();
    
    if ($('.flexacademy-quiz-question-slide').length > 0) {
        currentQuestionIndex = 0;
        updateQuizNavigation();
    }
    
    $(document).on('click', '.flexacademy-option-label', function(e) {
        const $radio = $(this).find('input[type="radio"]');
        if ($radio.length) {
            $radio.prop('checked', true);
            $(this).find('.flexacademy-option-text').addClass('checked');
            const radioName = $radio.attr('name');
            $('input[name="' + radioName + '"]').not($radio).each(function() {
                $(this).closest('.flexacademy-option-label').find('.flexacademy-option-text').removeClass('checked');
            });
        }
    });
    
    $('#quiz-next-btn, #quiz-next-side').on('click', function() {
        const totalQuestions = $('.flexacademy-quiz-question-slide').length;
        if (currentQuestionIndex < totalQuestions - 1) {
            currentQuestionIndex++;
            updateQuizNavigation();
        }
    });
    
    $('#quiz-back-btn, #quiz-prev-side').on('click', function() {
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            updateQuizNavigation();
        }
    });
});
