import React, { Component } from 'react';
import { Input, Label } from "reactstrap";
import { connect } from "react-redux";
//actions
import * as actionCreators from "../../../redux/SecureText/actions";
//components
import ContactPresence from "./../components/ContactPresence";
import LoadingSpinner from "./LoadingSpinner";

//use sortedContacts variable as global variable to sort contacts
let sortedContacts = [
]

class SelectContact extends Component {
    constructor(props) {
        super(props);
        this.state = {
            contacts: this.props.contacts,
            loading: this.props['args'].loading,
            childSearchKeywords: this.props['args'].searchKeywords,
            selectedContact: this.props['args'].selectedContact,
            isSideBar: this.props['args'].isSideBar,
            sortedContacts: []
        }
        this.sortContact = this.sortContact.bind(this);
        //const { handleSearchVal } = this.props;
    }

    componentDidUpdate(prevProps) {
        if (prevProps !== this.props) {
            //this.fetchContactList();
            this.setState({
                //contacts: this.props.contacts
                contacts: this.sortContact(),
                loading: this.state.loading
            });
        }
    }

    sortContact() {
        let data = this.state.contacts.reduce((r, e) => {
            try {
                e.checkedVal = false;
                let selectContacts = this.state.selectedContact;
                Object.keys(selectContacts).forEach(function(key) {
                    if(selectContacts[key]['id'] == e.id){
                        e.checkedVal = true;
                    }
                });
                // get first letter of name of current element
                let group = e.name[0].toUpperCase();
                // if there is no property in accumulator with this letter create it
                if (!r[group]) r[group] = { group, children: [e] }
                // if there is push current element to children array for that letter
                else r[group].children.push(e);
            } catch (error) {
                return sortedContacts;
            }
            // return accumulator
            return r;
        }, {})
        // since data at this point is an object, to get array of values
        // we use Object.values method
        let result = Object.values(data);
        result.sort((a, b) => a.group.localeCompare(b.group));  // Re sort the result afer putting into object because object.values destroy the sorting
        this.setState({ contacts: result });
        sortedContacts = result;
        return result;
    }

    componentDidMount() {
        this.fetchContactList();
        /* this.sortContact(); */
    }

    fetchContactList(){
        this.setState({ loading: true });
        let term = "";
        if(this.state.childSearchKeywords && this.state.childSearchKeywords.length > 1){
            term = this.state.childSearchKeywords;
        }

        const fetchContacts = this.props.fetchRecipientsList(term);
        const updateData = fetchContacts.then(result => {
            this.setState({ contacts: result.contacts });
            this.setState({ loading: false });
            this.setState({ sortedContacts: this.sortContact() });
            //this.sortContact();
        });
    }

    componentWillUnmount() {
        // this.setState({
        //     childSearchKeywords: ""
        // });
        // this.props.handleSearchVal(this.state.childSearchKeywords);
        //this.sortContact();
    }

    render() {
        return (
            <React.Fragment>
            {
                this.state.loading ? <LoadingSpinner /> :
                    this.state.sortedContacts.length > 0 ?
                        this.state.sortedContacts.map((contact, key) =>
                            <div key={key}>
                                <div className="font-weight-bold text-primary">
                                    {contact.group}
                                </div>

                                <ul className="list-unstyled contact-list">
                                    {
                                        contact.children.map((child, keyChild) =>
                                            <li key={keyChild} onClick={(e) => this.props.handleContactClick(e, child.id, child.name, child.presence)}>
                                                <div className="form-check">
                                                    {
                                                        this.state.isSideBar ? "" :
                                                            <Input type="checkbox" defaultChecked={child.checkedVal ? true : ""} disabled={(typeof this.props.users[this.props.active_user] !== 'undefined' && typeof this.props.users[this.props.active_user].priv !== 'undefined' && this.props.users[this.props.active_user].priv.remove==false && child.checkedVal) ? true : false} className="form-check-input" onChange={(e) => this.props.handleCheck(e, child.id, child.presence, child.covId)} id={"memberCheck" + child.id} value={child.name} />
                                                    }
                                                    <Label className="form-check-label" htmlFor={"memberCheck" + child.id}>
                                                        <ContactPresence args={[ {presence: child.presence, id: child.id, isCovered: child.isCovered || child.covId ? true : false} ]} />{child.name}
                                                    </Label>
                                                    {
                                                        this.state.isSideBar ? "" :
                                                            <ul className="list-unstyled contact-detail">
                                                                <li><strong>Degree:</strong><span>{ child.degree == null ? "N/A" : child.degree }</span></li>
                                                                <li className="ml-10"><strong>Department:</strong><span>{ child.dept == null ? "N/A" : child.dept }</span></li>
                                                                <li className="ml-10"><strong>Speciality:</strong><span>{ child.specialty == null ? "N/A" : child.specialty }</span></li>
                                                                <li className="ml-10"><strong>Practice:</strong><span>{ child.agname == null ? "N/A" : child.agname }</span></li>
                                                            </ul>
                                                    }

                                                </div>
                                            </li>
                                        )
                                    }
                                </ul>
                                {this.state.sortedContacts.length === key + 1 ? <label className="form-check-label contact-bottom-label">Please use search to find more users</label> : ""}    
                            </div>
                        ) : "No contact found"
            }
            </React.Fragment>
        );
    }
}

const mapStateToProps = (state) => {
const { users, contacts, active_user } = state.secureText.Chat;
return { users, contacts, active_user };
};

export default (connect(mapStateToProps, actionCreators)(SelectContact));