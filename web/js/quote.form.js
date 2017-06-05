
var $collectionHolder;

// setup an "add a section" link
var $addSectionLink = $('<a href="#" class="add_section_link">Add a section</a>');
var $newLinkLi = $('<li></li>').append($addSectionLink);

jQuery(document).ready(function() {
    // Get the ul that holds the collection of sections
    $collectionHolder = $('ul.sections');

    // add the "add a section" anchor and li to the sections ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    $addSectionLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new section form (see next code block)
        addSectionForm($collectionHolder, $newLinkLi);
    });
});

function addSectionForm($collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a section" link li
    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);
}

/**
 * When the customer select is updated: trigger customer details UI update
 */
function onCustomerSelected(select, baseUrl) {
    var userId = $(select[0].selectedOptions[0]).val();
    $.getJSON(baseUrl + '/' + userId, function(result) {
        if (result === undefined || result.user === undefined) {
            return;
        }

        $('#sections_container').show(250);
        $('#customer_info').show(250);
        $.each($('*[type=submit]'), function(i, input) {
            $(input).removeClass('disabled');
        });
        $.each(result.user.custom_fields, function(i, customField){
            if ("siret" === customField.name) {
                $('#siret').html(customField.value)
            } else if ("address" === customField.name) {
                $('#address').html(customField.value)
            }
        });
    });
}

/**
 * When the customer select is updated: trigger customer details UI update
 */
function onProjectSelected(select, baseUrl, customersSelector) {
    var projectId = $(select[0].selectedOptions[0]).val();
    $.getJSON(baseUrl + '/' + projectId, function(result) {
        if (result === undefined) {
            return;
        }

        // Show project info
        if (result.project !== undefined) {
            $('#project_info').show(250);
            var link = $('#project_name');
            link.html(result.project.name);
            link.attr('href', link.data('redmine-url') + '/projects/' + result.project.identifier);
        }

        // Fill customers select (filter by role)
        $.each(result.memberships, function (i, membership) {
            $.each(membership.roles, function(j, role) {
                if (role.id === 5) { //customers
                    $('<option/>')
                        .val(membership.user.id)
                        .html(membership.user.name)
                        .appendTo($(customersSelector));
                    $(customersSelector).val(membership.user.id).change();
                    return false;
                }
            });
        });
        $(customersSelector).prop('disabled', false);
    });
}
