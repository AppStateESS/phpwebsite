var ManagerSignUp = React.createClass({
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
            button = <button className="btn btn-primary">Submit account request</button>;
        } else {
            button = <button disabled={true} className="btn btn-warning">Complete all required fields to continue</button>;
        }

        return (
            <form action="index.php" method="post">
                <input type="hidden" name="module" value="properties" />
                <input type="hidden" name="cop" value="submitManagerApplication" />
                <p>
                    Please complete the form below to apply for a contact manager account.<br />
                    <i className="fa fa-asterisk text-danger"></i> Required input
                </p>
                <AccountInfo status={this.updateAccountInfoStatus}/>
                <ContactInfo status={this.updateContactInfoStatus}/>
                <div className="well">
                    <ManagerType updateManagerType={this.updateManagerType} managerType={this.state.managerType} />
                    <CompanyInfo hide={this.state.managerType} status={this.updateCompanyInfoStatus}/>
                </div>
                <div className="text-center">
                    {button}
                </div>
            </form>
        );
    }
});


var AccountInfo = React.createClass({
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
            <fieldset>
                <legend>Account info</legend>
                <Username status={this.updateUsernameStatus} />
                <Password status={this.updatePasswordStatus} />
            </fieldset>
        );
    }
});

var Username = React.createClass({

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
            status = <div><i className="fa fa-lg fa-spinner fa-spin"></i> Searching for duplicate username...</div>;
        } else if (this.state.error) {
            status = <div className="alert alert-danger"><i className="fa fa-lg fa-exclamation-circle"></i> {this.state.message}</div>;
        } else if (this.state.accepted) {
            status = <div className="alert alert-success"><i className="fa fa-lg fa-thumbs-o-up"></i> Username allowed</div>;
        }

        return (
            <div className="row">
                <div className="col-sm-12">
                    <TextInput label={'Username'} inputId={'managerUsername'} placeholder={'Enter preferred username. No spaces.'}
                        handleBlur={this.handleBlur} required={true} handlePress={this.preventSpaces}/>
                    {status}
                </div>
            </div>
        );
    }
});

var Password = React.createClass({
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
        if (this.state.lengthError) {
            alert = <div className="alert alert-danger"><i className="fa fa-exclamation-circle"></i> Passwords must be greater than 8 characters</div>;
        } else if (this.state.password !== this.state.pwCheck) {
            alert = <div className="alert alert-danger"><i className="fa fa-exclamation-circle"></i> Passwords do not match</div>;
        } else if (this.state.password.length !== 0 && this.state.password === this.state.pwCheck) {
            alert = <div className="alert alert-success"><i className="fa fa-check"></i> Password accepted. Be sure to write it down.</div>;
        } else {
            alert = null;
        }
        return (
            <div className="row">
                <div className="col-sm-6">
                    <PasswordInput inputId={'managerPassword'} label={'Password'} handleBlur={this.handleBlur} placeholder="Password must greater than 8 characters"/>
                </div>
                <div className="col-sm-6">
                    <PasswordInput inputId={'managerPasswordCompare'} label={'Retype password'} handleBlur={this.handleBlur}/>
                </div>
                <div className="col-sm-12">{alert}</div>
            </div>
        );
    }
});

var ManagerType = React.createClass({
    handleChange : function(event) {
        var change = event.target.value;
        this.props.updateManagerType(change);
    },

    render : function() {
        return (
            <div className="row">
                <div className="col-sm-4">
                    <p>
                    <label>
                        <input type="radio" name="managerType" value={true} defaultChecked={true} onChange={this.handleChange}/> Private Renter
                    </label>
                    </p>
                    <p>
                    <label>
                        <input type="radio" name="managerType" value={false} onChange={this.handleChange}/> Company
                    </label>
                    </p>
                </div>
                <div className="col-sm-6">
                    <p>Are you representing a property management company or are you a private renter?</p>
                </div>
            </div>
        );
    }
});

var CompanyInfo = React.createClass({
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
            <fieldset style={style}>
                <legend>Company info</legend>
                <CompanyName status={this.updateNameStatus} />
                <CompanyUrl status={this.updateUrlStatus}/>
                <CompanyAddress status={this.updateAddressStatus}/>
            </fieldset>
        );
    }
});

var CompanyName = React.createClass({
    render : function() {
        return (
            <TextInput label={'Company name'} inputId={'companyName'} required={true} handleBlur={this.props.status}/>
        );
    }
});

var CompanyUrl = React.createClass({
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
            <div>
                <TextInput label={'Company url'} inputId={'companyUrl'} handleBlur={this.handleBlur}/>
                {this.state.error ? <div className='alert alert-danger'><i className="fa fa-lg fa-exclamation-circle"></i> Improperly formatted url.</div> : null}
            </div>
        );
    }
});

var CompanyAddress = React.createClass({
    render : function() {
        return (
            <div>
                <TextInput label={'Company address'} inputId={'companyAddress'} placeholder={'Box, Street, City, State, Zip'} required={true} handleBlur={this.props.status}/>
            </div>
        );
    }
});

var ContactInfo = React.createClass({
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
            <fieldset>
                <legend>Contact info</legend>
                <div className="row">
                    <div className="col-sm-6">
                        <TextInput label={'First name'} inputId={'contactFirstName'} required={true} handleBlur={this.firstNameStatus}/>
                    </div>
                    <div className="col-sm-6">
                        <TextInput label={'Last name'} inputId={'contactLastName'} required={true} handleBlur={this.lastNameStatus}/>
                    </div>
                </div>
                <div className="row">
                    <div className="col-sm-6">
                        <TextInput label={'Email address'} inputId={'emailAddress'} required={true} handleBlur={this.emailStatus}/>
                    </div>
                    <div className="col-sm-6">
                        <TextInput label={'Phone number'} inputId={'phoneNumber'} required={true} handleBlur={this.phoneStatus}/>
                    </div>
                </div>
                <TextInput label={'Hours available for call'} inputId={'contactHours'}/>
            </fieldset>
        );
    }
});


var TextInput = React.createClass({
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
                required = <i className="fa fa-asterisk text-danger"></i>;
            }
            label = <label htmlFor={this.props.inputId}>{this.props.label}</label>;
        } else {
            label = null;
        }
        return (
            <div className="form-group">
                {label} {required}
                <input type="text" className="form-control" id={this.props.inputId} ref={this.props.inputId}
                    name={this.props.inputId} placeholder={this.props.placeholder} onFocus={this.handleFocus}
                    onChange={this.props.handleChange} onBlur={this.handleBlur} onKeyPress={this.props.handlePress}/>
            </div>
        );
    }
});

var PasswordInput = React.createClass({
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
            required = <i className="fa fa-asterisk text-danger"></i>;
            label = <label htmlFor={this.props.inputId}>{this.props.label}</label>;
        } else {
            label = null;
        }
        return (
            <div className="form-group">
                {label} {required}
                <input type="password" className="form-control" id={this.props.inputId} name={this.props.inputId} onBlur={this.props.handleBlur} placeholder={this.props.placeholder}/>
            </div>
        );
    }
});

React.render(<ManagerSignUp />, document.getElementById('manager-signup'));
