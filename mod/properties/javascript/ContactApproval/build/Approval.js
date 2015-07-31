var ContactApproval = React.createClass({displayName: "ContactApproval",

    getInitialState: function() {
        return {
            contactList : []
        };
    },

    componentWillMount: function() {
        $.getJSON('index.php', {
            module : 'properties',
            authkey: authkey,
            aop: 'approvalList'
        }).done(function(data){
            this.setState({
                contactList : data
            });
        }.bind(this));
    },

    removeContact : function(key) {
        var contacts = this.state.contactList;
        contacts.splice(key,1);
        this.setState({
            contactList : contacts
        });
    },

    render : function() {
        if (this.state.contactList.length === 0) {
            return React.createElement("p", null, "No new contacts need accepting.");
        } else {
            return (
                React.createElement(ApprovalItem, {listing: this.state.contactList, removeContact: this.removeContact})
            );
        }
    }
});

var ApprovalItem = React.createClass({displayName: "ApprovalItem",

    approve : function(key) {
        var contact = this.props.listing[key];
        this.props.removeContact(key);
        console.log('approve');
        $.getJSON('index.php', {
            module : 'properties',
            aop : 'approveContact',
            contactId : contact.id,
            authkey : authkey
        }).done(function(data){
            console.log(data);
        }.bind(this));
    },

    disapprove : function(key) {
        var contact = this.props.listing[key];
        this.props.removeContact(key);
        $.getJSON('index.php', {
            module : 'properties',
            aop : 'disapproveContact',
            contactId : contact.id,
            authkey : authkey
        }).done(function(data){
            console.log(data);
        }.bind(this));
    },

    render : function() {
        var applyDate = new Date();
        return (
            React.createElement("div", null,
                React.createElement("ul", {className: "list-group small"},
                    this.props.listing.map(function(value, key){
                        applyDate.setTime(value.last_log * 1000);
                        return (
                            React.createElement("li", {key: key, className: "list-group-item"},
                                React.createElement("div", {className: "row"},
                                    React.createElement("div", {className: "col-sm-12"},
                                        React.createElement("strong", null, "Application date:"), " ", applyDate.toDateString()
                                    )
                                ),
                                React.createElement("div", {className: "row"},
                                    React.createElement("div", {className: "col-sm-6"},
                                        React.createElement("strong", null, "Username:"), " ", value.username, React.createElement("br", null)
                                    ),
                                    React.createElement("div", {className: "col-sm-6"},
                                        React.createElement("strong", null, "Name:"), " ", value.first_name, " ", value.last_name, React.createElement("br", null)
                                    )
                                ),
                                React.createElement("div", {className: "row"},
                                    React.createElement("div", {className: "col-sm-6"},
                                        React.createElement("strong", null, "Email:"), " ", React.createElement("a", {href: 'mailto:' + value.email_address}, value.email_address)
                                    ),
                                    React.createElement("div", {className: "col-sm-6"},
                                        React.createElement("strong", null, "Phone:"), " ", value.phone
                                    )
                                ),
                                React.createElement(CompanyInfo, {contactInfo: value}),
                                React.createElement("button", {className: "btn btn-success", style: {marginRight: '1em'}, onClick: this.approve.bind(this,key)}, React.createElement("i", {className: "fa fa-check"}), " Accept"),
                                React.createElement("button", {className: "btn btn-danger", onClick: this.disapprove.bind(this,key)}, React.createElement("i", {className: "fa fa-times"}), " Decline")
                            )
                        );
                    }, this)
                )
            )
        );
    }
});

var CompanyInfo = React.createClass({displayName: "CompanyInfo",
    render : function() {
        if (this.props.contactInfo.private === '1') {
            return (
                React.createElement("div", null, React.createElement("strong", null, "Private Renter"))
            );
        } else {
            return (
                React.createElement("div", null,
                    React.createElement("div", {className: "row"},
                        React.createElement("div", {className: "col-sm-12"},
                            React.createElement("strong", null, "Company name:"), " ", this.props.contactInfo.company_name
                        ),
                        React.createElement("div", {className: "col-sm-12"},
                            React.createElement("strong", null, "Company url:"), " ", React.createElement("a", {href: this.props.contactInfo.company_url, target: "_blank"}, this.props.contactInfo.company_url)
                        ),
                        React.createElement("div", {className: "col-sm-12"},
                            React.createElement("strong", null, "Company address:"), " ", this.props.contactInfo.company_address
                        )
                    )
                )
            );
        }

    }
});

$(window).load(function(){
    React.render(React.createElement(ContactApproval, null), document.getElementById('ContactApproval'));
});
