var ContactApproval = React.createClass({

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
            return <p>No new contacts need accepting.</p>;
        } else {
            return (
                <ApprovalItem listing={this.state.contactList} removeContact={this.removeContact}/>
            );
        }
    }
});

var ApprovalItem = React.createClass({

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
            <div>
                <ul className="list-group small">
                    {this.props.listing.map(function(value, key){
                        applyDate.setTime(value.last_log * 1000);
                        return (
                            <li key={key} className="list-group-item">
                                <div className="row">
                                    <div className="col-sm-12">
                                        <strong>Application date:</strong> {applyDate.toDateString()}
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-sm-6">
                                        <strong>Username:</strong> {value.username}<br />
                                    </div>
                                    <div className="col-sm-6">
                                        <strong>Name:</strong> {value.first_name} {value.last_name}<br />
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-sm-6">
                                        <strong>Email:</strong> <a href={'mailto:' + value.email_address}>{value.email_address}</a>
                                    </div>
                                    <div className="col-sm-6">
                                        <strong>Phone:</strong> {value.phone}
                                    </div>
                                </div>
                                <CompanyInfo contactInfo={value} />
                                <button className="btn btn-success" style={{marginRight: '1em'}} onClick={this.approve.bind(this,key)}><i className="fa fa-check"></i> Accept</button>
                                <button className="btn btn-danger" onClick={this.disapprove.bind(this,key)}><i className="fa fa-times" ></i> Decline</button>
                            </li>
                        );
                    }, this)}
                </ul>
            </div>
        );
    }
});

var CompanyInfo = React.createClass({
    render : function() {
        if (this.props.contactInfo.private === '1') {
            return (
                <div><strong>Private Renter</strong></div>
            );
        } else {
            return (
                <div>
                    <div className="row">
                        <div className="col-sm-12">
                            <strong>Company name:</strong> {this.props.contactInfo.company_name}
                        </div>
                        <div className="col-sm-12">
                            <strong>Company url:</strong> <a href={this.props.contactInfo.company_url} target="_blank">{this.props.contactInfo.company_url}</a>
                        </div>
                        <div className="col-sm-12">
                            <strong>Company address:</strong> {this.props.contactInfo.company_address}
                        </div>
                    </div>
                </div>
            );
        }

    }
});

React.render(<ContactApproval />, document.getElementById('ContactApproval'));
