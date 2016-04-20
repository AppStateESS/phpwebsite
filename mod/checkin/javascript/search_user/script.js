/*
 * Copyright (C) 2016 matt.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

$(document).ready(function () {
    $("#edit-staff_username").autocomplete(
            {
                delay: 500,
                minLength: 2,
                dataType: 'json',
                source: function (request, response) {
                    $.getJSON('index.php', {
                        module: 'checkin',
                        aop: 'search_users',
                        q: request.term
                    }).success(function (data) {
                        response(data);
                    });
                },
            }
    );

});
