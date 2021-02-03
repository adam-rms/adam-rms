$(document).ready(function () {
    {% if 21|instancePermissions %}
    $(".newProjectButton").click(function () {
        bootbox.prompt({
            title: "Select Project Type",
            inputType: 'select',
            inputOptions: [
                {% for type in potentialProjectTypes %}
        {
            text: '{{type.projectsTypes_name}}',
                value: '{{ type.projectsTypes_id }}',
        },
        {% endfor %}
    ],
        callback: function (result) {
            if (result) {
                var projectType = result;
                bootbox.prompt({
                    title: "Select Project Manager",
                    inputType: 'select',
                    value: {{ USERDATA.users_userid }},
                inputOptions: [
                    {% for user in potentialProjectManagers %}
                {
                    text: '{{ user.users_name1 ~ " " ~ user.users_name2 }}',
                        value: '{{ user.users_userid }}',
                },
                {% endfor %}
            ],
                callback: function (result) {
                    if (result) {
                        var projectManager = result;
                        bootbox.prompt("Enter Project Name", function (result) {
                            if (result) {
                                ajaxcall("projects/new.php", {
                                    "projects_name": result,
                                    "projectsType_id": projectType,
                                    "projects_manager": projectManager
                                }, function (data) {
                                    window.location.href = "{{ CONFIG.ROOTURL }}/project/?id=" + data.response['projects_id'];
                                });
                            }
                        });
                    }
                }
            });
            }
        }
    });
    });
    {% endif %}

});