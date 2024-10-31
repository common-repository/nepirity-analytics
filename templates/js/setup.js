function np_update_select_data(select_id, profiles) {
    var select = document.getElementById(select_id);

    if (select) {

        while (select.options.length) {
            select.remove(0);
        }

        for (var i=0; i < profiles.length; i++) {
            var option = document.createElement("option");
            option.value = profiles[i].id;
            option.text = profiles[i].name;
            select.add(option);
        }
    }
}

function np_get_properties(profiles, account_id) {
    for (var i=0; i < profiles.length; i++) {
        if (profiles[i].id == account_id) {
            return profiles[i].properties;
        }
    }
}

function np_get_views(profiles, account_id, property_id) {
    for (var i=0; i < profiles.length; i++) {
        if (profiles[i].id == account_id) {
            for (var j=0; j < profiles[i].properties.length; j++) {
                if (profiles[i].properties[j].id == property_id) {
                    return profiles[i].properties[j].views;
                }
            }
        }
    }
}

function np_current_select_value(select_id) {
    var select = document.getElementById(select_id);

    if (select) {
        var index = select.selectedIndex;
        if (index < 0) index = 0;
        return (select.options[index].value);
    }
}

function np_select_init(select) {
    var id = select.id;

    if (id == 'account' || id == 'property') {
        var account_id = np_current_select_value('account');

        if (id == 'account') {
            np_update_select_data('property', np_get_properties(profiles, account_id));
        }

        var property_id = np_current_select_value('property');
        np_update_select_data('view', np_get_views(profiles, account_id, property_id));
    }
}

function np_set_selected_option(elmnt, value) {
    for(var i=0; i < elmnt.options.length; i++) {
        if(elmnt.options[i].value === value) {
            elmnt.selectedIndex = i;
            break;
        }
    }
}

