var ManagerSignUp = React.createClass({displayName: "ManagerSignUp",
    mixins: [React.addons.PureRenderMixin],

    getInitialState: function() {
        return {
            accountInfoStatus : false,
            contactInfoStatus: false,
            companyInfoStatus:false,
            managerType : true
        };
    },

    updateAccountInfoStatus : function(value)
    {
        this.setState({
            accountInfoStatus : value === 'true' || value === true
        });
    },

    updateContactInfoStatus : function(value)
    {
        this.setState({
            contactInfoStatus : value === 'true' || value === true
        });
    },

    updateCompanyInfoStatus : function(value)
    {
        this.setState({
            companyInfoStatus : value === 'true' || value === true
        });
    },

    updateManagerType : function(value) {
        this.setState({
            managerType: value === 'true' || value === true
        });
    },

    render: function() {
        var ready = this.state.accountInfoStatus && this.state.contactInfoStatus && (this.state.managerType || this.state.companyInfoStatus);
        var style = {display : this.state.managerType === true ? 'none' : 'block'};
        var button = null;
        if (ready) {
            button = React.createElement("button", {className: "btn btn-primary"}, "Submit account request");
        } else {
            button = React.createElement("button", {disabled: true, className: "btn btn-warning"}, "Complete all required fields to continue");
        }

        return (
            React.createElement("form", {action: "index.php", method: "post"},
                React.createElement("input", {type: "hidden", name: "module", value: "properties"}),
                React.createElement("input", {type: "hidden", name: "cop", value: "submitManagerApplication"}),
                React.createElement("p", null,
                    "Please complete the form below to apply for a contact manager account.", React.createElement("br", null),
                    React.createElement("i", {className: "fa fa-asterisk text-danger"}), " Required input"
                ),
                React.createElement(AccountInfo, {status: this.updateAccountInfoStatus}),
                React.createElement(ContactInfo, {status: this.updateContactInfoStatus}),
                React.createElement("div", {className: "well"},
                    React.createElement(ManagerType, {updateManagerType: this.updateManagerType, managerType: this.state.managerType}),
                    React.createElement(CompanyInfo, {hide: this.state.managerType, status: this.updateCompanyInfoStatus})
                ),
                React.createElement("div", {className: "text-center"},
                    button
                )
            )
        );
    }
});


var AccountInfo = React.createClass({displayName: "AccountInfo",
    mixins: [React.addons.PureRenderMixin],

    getInitialState: function() {
        return {
            userStatus : false,
            passwordStatus : false
        };
    },

    updateUsernameStatus : function(status) {
        this.setState({
            userStatus : status === 'true' || status === true
        });
    },

    updatePasswordStatus : function(status) {
        this.setState({
            passwordStatus : status === 'true' || status === true
        });
    },

    componentDidUpdate : function(prevProps, prevState) {
        this.props.status(this.state.userStatus && this.state.passwordStatus);
    },

    render : function() {
        return (
            React.createElement("fieldset", null,
                React.createElement("legend", null, "Account info"),
                React.createElement(Username, {status: this.updateUsernameStatus}),
                React.createElement(Password, {status: this.updatePasswordStatus})
            )
        );
    }
});

var Username = React.createClass({displayName: "Username",

    getInitialState: function() {
        return {
            waiting: false,
            duplicate : false,
            error : false,
            message: null,
            accepted: false
        };
    },

    handleBlur : function(event) {
        var username = event.target.value;

        if (username.length < 4) {
            this.setState({
                error : true,
                waiting: false,
                message : 'Please choose a longer username'
            });
            this.props.status(false);
            return;
        }

        this.setState({
            waiting : true,
        });

        $.getJSON('index.php', {
            module: 'properties',
            cop : 'checkUsername',
            username : username
        }).done(function(data){
            if (data.error !== undefined) {
                this.setState({
                    error : true,
                    waiting: false,
                    message : 'An error occurred when trying to check your username'
                });
                this.props.status(false);
            }
            if (data.result) {
                this.setState({
                    error : true,
                    waiting: false,
                    message : 'Please choose a different username'
                });
                this.props.status(false);
            } else {
                this.setState({
                    error : false,
                    waiting: false,
                    accepted: true
                });
                this.props.status(true);
            }
        }.bind(this)).fail(function(){
            this.setState({
                error : true,
                waiting: false,
                message : 'Username not allowed'
            });
            this.props.status(false);
        }.bind(this));
    },

    preventSpaces : function(e) {
        if (e.which === 32) {
            e.preventDefault();
        }
    },

    render : function() {
        var status;

        if (this.state.waiting) {
            status = React.createElement("div", null, React.createElement("i", {className: "fa fa-lg fa-spinner fa-spin"}), " Searching for duplicate username...");
        } else if (this.state.error) {
            status = React.createElement("div", {className: "alert alert-danger"}, React.createElement("i", {className: "fa fa-lg fa-exclamation-circle"}), " ", this.state.message);
        } else if (this.state.accepted) {
            status = React.createElement("div", {className: "alert alert-success"}, React.createElement("i", {className: "fa fa-lg fa-thumbs-o-up"}), " Username allowed");
        }

        return (
            React.createElement("div", {className: "row"},
                React.createElement("div", {className: "col-sm-12"},
                    React.createElement(TextInput, {label: 'Username', inputId: 'managerUsername', placeholder: 'Enter preferred username. No spaces.',
                        handleBlur: this.handleBlur, required: true, handlePress: this.preventSpaces}),
                    status
                )
            )
        );
    }
});

var Password = React.createClass({displayName: "Password",
    getInitialState: function() {
        return {
            password : '',
            pwCheck : '',
            lengthError : false
        };
    },

    handleBlur : function(event) {
        var inputName = event.target.name;
        var inputValue = event.target.value;

        if (inputName === 'managerPassword') {
            if (inputValue.length < 8) {
                this.setState({
                    lengthError : true
                });
                this.props.status(false);
                return;
            } else if (this.state.lengthError === true) {
                this.setState({
                    lengthError : false
                });
            }
            if (inputValue === this.state.pwCheck) {
                this.props.status(true);
            } else {
                this.props.status(false);
            }
            this.setState({
                password : inputValue
            });
        } else if (inputName === 'managerPasswordCompare') {
            if (inputValue === this.state.password) {
                this.props.status(true);
            } else {
                this.props.status(false);
            }
            this.setState({
                pwCheck : inputValue
            });
        }
    },

    render : function() {
        var alert = null;
            alert = React.createElement("div", {className: "alert alert-danger"}, React.createElement("i", {className: "fa fa-exclamation-circle"}), " Passwords must be greater than 8 characters");
        if (this.state.lengthError) {
        } else if (this.state.password !== this.state.pwCheck) {
            alert = React.createElement("div", {className: "alert alert-danger"}, React.createElement("i", {className: "fa fa-exclamation-circle"}), " Passwords do not match");
        } else {
            alert = null;
        }
        return (
            React.createElement("div", {className: "row"},
                React.createElement("div", {className: "col-sm-6"},
                    React.createElement(PasswordInput, {inputId: 'managerPassword', label: 'Password', handleBlur: this.handleBlur, placeholder: "Password must greater than 8 characters"})
                ),
                React.createElement("div", {className: "col-sm-6"},
                    React.createElement(PasswordInput, {inputId: 'managerPasswordCompare', label: 'Retype password', handleBlur: this.handleBlur})
                ),
                React.createElement("div", {className: "col-sm-12"}, alert)
            )
        );
    }
});

var ManagerType = React.createClass({displayName: "ManagerType",
    handleChange : function(event) {
        var change = event.target.value;
        this.props.updateManagerType(change);
    },

    render : function() {
        return (
            React.createElement("div", {className: "row"},
                React.createElement("div", {className: "col-sm-4"},
                    React.createElement("p", null,
                    React.createElement("label", null,
                        React.createElement("input", {type: "radio", name: "managerType", value: true, defaultChecked: true, onChange: this.handleChange}), " Private Renter"
                    )
                    ),
                    React.createElement("p", null,
                    React.createElement("label", null,
                        React.createElement("input", {type: "radio", name: "managerType", value: false, onChange: this.handleChange}), " Company"
                    )
                    )
                ),
                React.createElement("div", {className: "col-sm-6"},
                    React.createElement("p", null, "Are you representing a property management company or are you a private renter?")
                )
            )
        );
    }
});

var CompanyInfo = React.createClass({displayName: "CompanyInfo",
    getInitialState: function() {
        return {
            nameStatus : false,
            addressStatus : false,
            urlStatus : true
        };
    },

    updateNameStatus : function(e) {
        this.setState({
            nameStatus : e.target.value.length > 0
        });
    },

    updateAddressStatus : function(e) {
        this.setState({
            addressStatus : e.target.value.length > 0
        });
    },

    updateUrlStatus : function(status) {
        this.setState({
            urlStatus : status
        });
    },

    componentDidUpdate : function(prevProps, prevState) {
        this.props.status(this.state.nameStatus && this.state.addressStatus && this.state.urlStatus);
    },


    render : function() {
        var style = {display : this.props.hide === true ? 'none' : 'block'};
        return (
            React.createElement("fieldset", {style: style},
                React.createElement("legend", null, "Company info"),
                React.createElement(CompanyName, {status: this.updateNameStatus}),
                React.createElement(CompanyUrl, {status: this.updateUrlStatus}),
                React.createElement(CompanyAddress, {status: this.updateAddressStatus})
            )
        );
    }
});

var CompanyName = React.createClass({displayName: "CompanyName",
    render : function() {
        return (
            React.createElement(TextInput, {label: 'Company name', inputId: 'companyName', required: true, handleBlur: this.props.status})
        );
    }
});

var CompanyUrl = React.createClass({displayName: "CompanyUrl",
    getInitialState: function() {
        return {
            error : false
        };
    },

    plugHttp : function(target) {
        var matchUrl = /^https?:\/\//gi;
        var regex = new RegExp(matchUrl);
        if (!target.value.match(regex)) {
            target.value = 'http://' + target.value;
        }
    },

    handleBlur : function(event) {
        if (event.target.value.length === 0) {
            this.props.status(true);
            return;
        }
        var error = false;
        this.plugHttp(event.target);
        var matchUrl = /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi;
        var regex = new RegExp(matchUrl);
        if (!event.target.value.match(regex)) {
            this.props.status(false);
            this.setState({
                error : true
            });
        } else {
            this.props.status(true);
            this.setState({
                error : false
            });
        }
    },

    render : function() {
        return (
            React.createElement("div", null,
                React.createElement(TextInput, {label: 'Company url', inputId: 'companyUrl', handleBlur: this.handleBlur}),
                this.state.error ? React.createElement("div", {className: "alert alert-danger"}, React.createElement("i", {className: "fa fa-lg fa-exclamation-circle"}), " Improperly formatted url.") : null
            )
        );
    }
});

var CompanyAddress = React.createClass({displayName: "CompanyAddress",
    render : function() {
        return (
            React.createElement("div", null,
                React.createElement(TextInput, {label: 'Company address', inputId: 'companyAddress', placeholder: 'Box, Street, City, State, Zip', required: true, handleBlur: this.props.status})
            )
        );
    }
});

var ContactInfo = React.createClass({displayName: "ContactInfo",
    getInitialState: function() {
        return {
            firstNameStatus: false,
            lastNameStatus : false,
            emailStatus : false,
            phoneStatus : false
        };
    },

    firstNameStatus : function(e) {
        var value = e.target.value;
        this.setState({
            firstNameStatus : value.length > 0
        });
    },

    lastNameStatus : function (e) {
        var value = e.target.value;
        this.setState({
            lastNameStatus : value.length > 0
        });
    },

    emailStatus : function (e) {
        var value = e.target.value;
        this.setState({
            emailStatus : value.length > 0
        });
    },

    phoneStatus : function (e) {
        var value = e.target.value;
        this.setState({
            phoneStatus : value.length > 0
        });
    },

    componentDidUpdate : function(prevProps, prevState) {
        this.props.status(this.state.firstNameStatus && this.state.lastNameStatus &&
            this.state.emailStatus && this.state.phoneStatus);
    },

    render : function() {
        return (
            React.createElement("fieldset", null,
                React.createElement("legend", null, "Contact info"),
                React.createElement("div", {className: "row"},
                    React.createElement("div", {className: "col-sm-6"},
                        React.createElement(TextInput, {label: 'First name', inputId: 'contactFirstName', required: true, handleBlur: this.firstNameStatus})
                    ),
                    React.createElement("div", {className: "col-sm-6"},
                        React.createElement(TextInput, {label: 'Last name', inputId: 'contactLastName', required: true, handleBlur: this.lastNameStatus})
                    )
                ),
                React.createElement("div", {className: "row"},
                    React.createElement("div", {className: "col-sm-6"},
                        React.createElement(TextInput, {label: 'Email address', inputId: 'emailAddress', required: true, handleBlur: this.emailStatus})
                    ),
                    React.createElement("div", {className: "col-sm-6"},
                        React.createElement(TextInput, {label: 'Phone number', inputId: 'phoneNumber', required: true, handleBlur: this.phoneStatus})
                    )
                ),
                React.createElement(TextInput, {label: 'Hours available for call', inputId: 'contactHours'})
            )
        );
    }
});


var TextInput = React.createClass({displayName: "TextInput",
    getDefaultProps: function() {
        return {
            label: '',
            placeholder: '',
            handleChange: null,
            handleBlur:null,
            required: false,
            handlePress : null
        };
    },

    handleBlur : function(e) {
        if (this.props.required && e.target.value.length < 1) {
            $(e.target).css('border-color', 'red');
        }
        if (this.props.handleBlur) {
            this.props.handleBlur(e);
        }
    },

    handleFocus : function(e) {
        $(e.target).css('border-color', '');
    },

    render : function() {
        var label = '';
        var required = '';
        if (this.props.label.length > 0) {
            if (this.props.required) {
                required = React.createElement("i", {className: "fa fa-asterisk text-danger"});
            }
            label = React.createElement("label", {htmlFor: this.props.inputId}, this.props.label);
        } else {
            label = null;
        }
        return (
            React.createElement("div", {className: "form-group"},
                label, " ", required,
                React.createElement("input", {type: "text", className: "form-control", id: this.props.inputId, ref: this.props.inputId,
                    name: this.props.inputId, placeholder: this.props.placeholder, onFocus: this.handleFocus,
                    onChange: this.props.handleChange, onBlur: this.handleBlur, onKeyPress: this.props.handlePress})
            )
        );
    }
});

var PasswordInput = React.createClass({displayName: "PasswordInput",
    getDefaultProps: function() {
        return {
            label: '',
            placeholder : ''
        };
    },

    render : function() {
        var label = '';
        var required = '';
        if (this.props.label.length > 0) {
            required = React.createElement("i", {className: "fa fa-asterisk text-danger"});
            label = React.createElement("label", {htmlFor: this.props.inputId}, this.props.label);
        } else {
            label = null;
        }
        return (
            React.createElement("div", {className: "form-group"},
                label, " ", required,
                React.createElement("input", {type: "password", className: "form-control", id: this.props.inputId, name: this.props.inputId, onBlur: this.props.handleBlur, placeholder: this.props.placeholder})
            )
        );
    }
});

$(window).load(function(){
    React.render(React.createElement(ManagerSignUp, null), document.getElementById('manager-signup'));
});
