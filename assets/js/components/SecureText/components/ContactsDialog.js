import React, { Component } from 'react';
import { Button, Modal, ModalHeader, ModalBody, ModalFooter, UncontrolledTooltip, Form, Label, Input, Collapse, CardHeader, CardBody, Alert, InputGroup, Card, Badge } from 'reactstrap';
import { Link } from "react-router-dom";
import { connect } from "react-redux";

import { withTranslation } from 'react-i18next';

//simple bar
import SimpleBar from "simplebar-react";

//components
import SelectContact from "./../components/SelectContact";
import ContactPresence from "./../components/ContactPresence";
import LoadingSpinner from "./../components/LoadingSpinner";
import moment from 'moment';

//actions
import {
    createGroup,
    createTempGroup,
    setActiveTab,
    activeUser,
    activeGroup,
    createTempSingleChat
} from "../../../redux/SecureText/actions";

const modelBtnSingleMessageText = "Send Message";
const modelBtnGroupMessageText = "Create Group";
const modelBtnSingleGroupMessageText = "Send Message to Group";
class ContactsDialog extends Component {
    constructor(props) {
        super(props);
        this.state = {
            modal: false,
            isOpenCollapse: true,
            selectedContact: (this.props.selectedContact === undefined)? [] :this.props.selectedContact,
            currMembers: this.props.selectedContact,
            isOpenAlert: false,
            message: "",
            groupName: "",
            groupDesc: "",
            isGroupBtn: 0,
            displayGroup: "none",
            groupBtnLabel: "Create Group",
            //modalBtnLabel: modelBtnSingleMessageText,
            modalBtnLabel: "Initiate Chat",
            loading: true,
            Searchloading: false,
            searchContacts: false,
            searchKeywords: "",
            isFirstTimePageLoad: true,
            recentChatList: this.props.recentChatList,
            isFromSingleThread: (this.props.isFromSingleThread === undefined)? false :this.props.isFromSingleThread
        }
        this.toggle = this.toggle.bind(this);
        this.createGroup = this.createGroup.bind(this);
        this.handleCheck = this.handleCheck.bind(this);
        this.handleChangeGroupName = this.handleChangeGroupName.bind(this);
        this.handleChangeGroupDesc = this.handleChangeGroupDesc.bind(this);
        this.changeButtonLabel = this.changeButtonLabel.bind(this);
        this.checkGroupExistsInContacts = this.checkGroupExistsInContacts.bind(this);
        this.handleContactSearch = this.handleContactSearch.bind(this);
        this.onHandleSearchVal = this.onHandleSearchVal.bind(this);
        //this.deleteContact = this.deleteContact.bind(this);
        this.handleContactClick = this.handleContactClick.bind(this);
        this.initialSelected = [];
    }

    toggle() {
        this.initialSelected = $.extend( [], this.props.currMembers );
        this.setState({
            modal: !this.state.modal,
            searchKeywords: "",
            selectedContact: $.extend( [], this.state.currMembers )
        });
    }

    handleContactClick(){

    }


    componentDidUpdate(prevProps) {
        if (prevProps !== this.props) {
            this.setState({
                isGroupBtn: this.state.isGroupBtn,
                selectedContact: this.state.selectedContact,
                searchContacts: this.state.searchContacts,
                searchKeywords: this.state.searchKeywords,
                isFirstTimePageLoad: this.state.isFirstTimePageLoad,
                currMembers: this.props.selectedContact,
            });
        }
    }

    componentWillReceiveProps(nextProps) {
        if (this.props.recentChatList !== nextProps.recentChatList) {
            this.setState({
                recentChatList: nextProps.recentChatList,
            });
        }
    }

    createGroup() {
        let isSingleContactGroup = false;
        if (this.state.selectedContact.length === 1) {
            let contactInitialName = this.state.selectedContact[0].id.substring(0, 2);
            if(contactInitialName === "AG" || contactInitialName === "AD"){
                isSingleContactGroup = true;
            }
        }

        if (this.state.selectedContact.length === 0) {
            this.setState({message: "Please select at least one contact!", isOpenAlert: true});
        } else if (this.state.selectedContact.length === 1 && isSingleContactGroup === false) {       // Send Single User Message
            let contactObj = this.state.selectedContact[0];
            var obj = {
                id: contactObj.id,
                name: contactObj.name,
                isGroup: false,
                unRead: 0,
                isNew: true,
                desc: this.state.groupDesc,
                members: this.state.selectedContact,
                presence : contactObj.presence,
                saved: false,
                lastMessage: null,
                msgNew: 0,
                unread: 0,
                messages: [],
                isTempSingle: true
            }
            // Check if that contact is already exists in the recent chat or not?
            let isExistsInRecent = false;
            let activeUserKey = 0;
            this.state.recentChatList.map((recentChat, keyChat) => {
                if( recentChat.id == contactObj.id ){
                    activeUserKey = keyChat;
                    return isExistsInRecent = true;
                }
            })
            if(isExistsInRecent){   // contacts already exists in the recent lis
                //this.props.createTempSingleChat(obj);
                this.toggle();
                this.props.setActiveTab('chat');
                this.props.activeUser(activeUserKey);
            } else {
                this.props.createTempSingleChat(obj);
                this.toggle();
                this.props.setActiveTab('chat');
                this.props.activeUser(0);
            }
            

        } else if (this.state.selectedContact.length === 1 && isSingleContactGroup === true) {          // Send group message
            let contactObj = this.state.selectedContact[0];
            let gMembers = [{"id" : this.props.my_user, "name": this.props.my_name, "presence":this.props.my_presence }, ...this.state.selectedContact];
            // Create group message here
            var obj = {
                //groupId: this.state.groups.length + 1,
                id: -Math.abs(this.state.recentChatList.length + 1),        //-1,
                name: contactObj.name,
                isGroup: true,
                unRead: 0,
                isNew: true,
                desc: this.state.groupDesc,
                members: this.state.selectedContact,
                saved: false,
                presence : this.state.selectedContact[0].presence,
                lastMessage: null,
                msgNew: 0,
                unread: 0,
                messages: []
            }
            //call action for creating a group
            //this.props.createGroup(obj);
            this.props.createTempGroup(obj);
            this.toggle();
            this.props.setActiveTab('chat');
            this.props.activeUser(0);
        } else if (this.state.selectedContact.length > 1) {         // Send group message
            //fetchAndSetUsersMessagesThread
            // Create group message here
            let gMembers = [{"id" : this.props.my_user, "name": this.props.my_name, "presence":this.props.my_presence }, ...this.state.selectedContact];

            let firstNames = [];
            gMembers.forEach(function(member){
                if(member.presence && member.presence == "group"){
                    firstNames.push(member.name);
                }else{
                    firstNames.push(member.name.split(',')[1]);
                }
            });
            firstNames.shift();  //Remove first name i.e. my name.

            let grpName = firstNames.join(", ");
            //first names limited to under 110 characters
            if(grpName.length > 110){
                grpName = grpName.slice(0, 110);
                let index1 = grpName.lastIndexOf(",");
                grpName = grpName.slice(0, index1);
            }

            var obj = {
                //groupId: this.state.groups.length + 1,
                id: -Math.abs(this.state.recentChatList.length + 1),        //-1,
                name: grpName,
                isGroup: true,
                unRead: 0,
                isNew: true,
                desc: this.state.groupDesc,
                members: gMembers,
                priv: {leave: true, add:true, remove: true, rename: true},
                saved: false,
                lastMessage: null,
                msgNew: 0,
                unread: 0,
                messages: [],
                owner : this.props.my_user
            }
            //call action for creating a group
            //this.props.createGroup(obj);
            this.props.createTempGroup(obj);
            this.toggle();
            this.props.setActiveTab('chat');
            this.props.activeUser(0);
        }

        setTimeout(
            function () {
                this.setState({ isOpenAlert: false });
            }
                .bind(this),
            3000
        );
    }

    deleteContact(id, e){
        let selected = this.state.selectedContact;
        let readjustData = $.grep(selected, function(e){
            return e.id != id;
        });
        this.setState({ selectedContact: readjustData });
        // Uncheck checkbox
        $("#memberCheck"+id).prop("checked", false);
    }

    handleCheck(e, contactId, getPresence, coverageId = false) {
        if(this.state.selectedContact === undefined){
            var selected = [];
        } else {
            var selected = this.state.selectedContact;
        }

        var obj;
        if (e.target.checked) {
            obj = {
                id: contactId,
                name: e.target.value,
                covId: coverageId,
                presence: getPresence
            };
            selected.unshift(obj);  // pushed in to array at first position
            this.setState({ selectedContact: selected });
            this.changeButtonLabel(selected);
        } else {
            let readjustData = $.grep(selected, function(e){
                return e.id != contactId;
            });
            this.setState({ selectedContact: readjustData });
            this.changeButtonLabel(readjustData);
        }
    }

    changeButtonLabel(selectedContacts){
        // let checkGroupExists = this.checkGroupExistsInContacts(selectedContacts);
        // if(checkGroupExists === true && selectedContacts.length === 1){
        //     this.setState({ modalBtnLabel: modelBtnSingleGroupMessageText });
        // } else if(checkGroupExists === true){
        //     this.setState({ modalBtnLabel: modelBtnGroupMessageText });
        // } else {
        //     if (selectedContacts.length > 1) {
        //         this.setState({ modalBtnLabel: modelBtnGroupMessageText });
        //     } else {
        //         this.setState({ modalBtnLabel: modelBtnSingleMessageText });
        //     }
        // }
    }

    checkGroupExistsInContacts(contacts){
        let isGroupExists = false;
        $.each( contacts, function( index, value ){
            if($.isNumeric(value.id) === false){
                isGroupExists = true;
                return isGroupExists;
            }
        });
        return isGroupExists;
    }

    handleChangeGroupName(e) {
        this.setState({ groupName: e.target.value });
    }

    handleChangeGroupDesc(e) {
        this.setState({ groupDesc: e.target.value });
    }

    componentDidMount() {
        
    }

    handleContactSearch (e) {
        if(e.target.value.length > 2 || e.target.value.length === 0){
            this.setState({ Searchloading: true, loading: false });
            setTimeout(function(){ this.setState({ Searchloading: false }); }.bind(this), 1000);
            this.setState({
                searchContacts: true,
                searchKeywords: e.target.value,
                isFirstTimePageLoad: false,
            });
        } else{
            this.setState({
                searchContacts: false,
                searchKeywords: "",
                isFirstTimePageLoad: false,
            });

        }
    }

    onHandleSearchVal(searchVal){
        this.setState({
            searchContacts: true,
            loading: true,
            //searchKeywords: "",
           // selectedContact: []
        });

    }

    

    render() {
        const { t } = this.props;
        return (
            <React.Fragment>
                {
                    (!this.state.isFromSingleThread) ?
                        <Button onClick={this.toggle} type="button" color="link" style={{ color: "#00a89e"}} className="text-decoration-none font-size-22 py-0">
                            <i className="fas fa-comment-medical"></i>
                        </Button>
                        :
                        <Button type="button" title="Add members" color="none" onClick={this.toggle} className="nav-btn save-group-chat">
                            <i className="ri-user-add-line"></i>
                        </Button>
                }
                {/* Start add group Modal */}
                <Modal size="lg" isOpen={this.state.modal} centered toggle={this.toggle}>
                    <ModalHeader tag="h5" className="modal-title font-size-15 mb-0" toggle={this.toggle}>{t('Contacts')}</ModalHeader>
                    <ModalBody className="p-4">
                        <Form>
                            <div className="mb-4">
                                <Alert isOpen={this.state.isOpenAlert} color="danger">
                                    {this.state.message}
                                </Alert>

                                <ul className="contact-selected">
                                {
                                    (!this.state.selectedContact) ? "" :
                                    this.state.selectedContact.map((selContact, contactKey) =>
                                        <li className="contact-selected_li" key={selContact.id}>
                                            <span className="contact_selection_clear" role="presentation">
                                                <a href="#" onClick={this.deleteContact.bind(this, selContact.id)}>×</a>
                                            </span>
                                            <span className="ml-3">{selContact.name}
                                                <ContactPresence args={[ {presence: selContact.presence, id: selContact.id, isCovered: selContact.isCovered || selContact.covId ? true : false} ]} />
                                            </span>
                                        </li>
                                    )
                                }
                                </ul>
                                <div>
                                    <Input type="text" id="search-contacts-field" autoComplete="off" className="form-control form-control-lg bg-light border-light" placeholder="Please enter 3 or more characters to search contacts" onKeyUp={this.handleContactSearch} />
                                </div>
                                <SimpleBar style={{ maxHeight: "300px" }}>
                                    {/* contacts */}
                                    <div id="addContacts">
                                        {
                                            // this.state.isFirstTimePageLoad || this.state.searchKeywords !== ""  ?
                                            // <SelectContact
                                            //     args={{loading: this.state.loading, searchKeywords: this.state.searchKeywords, selectedContact: this.state.selectedContact, isSideBar: false}}
                                            //     handleCheck={this.handleCheck}
                                            //     handleSearchVal={this.onHandleSearchVal}
                                            //     handleContactClick={this.handleContactClick}
                                            // /> : "Please enter 3 or more characters to search contacts"
                                            
                                            this.state.isFirstTimePageLoad || this.state.searchKeywords === "" || this.state.searchKeywords.length > 2  ?
                                            <div>
                                                {
                                                    this.state.Searchloading ? <LoadingSpinner /> : 
                                                    <SelectContact
                                                        args={{loading:this.state.loading, searchKeywords:this.state.searchKeywords, selectedContact:this.state.selectedContact, isSideBar:false}}
                                                        handleCheck={this.handleCheck}
                                                        handleSearchVal={this.onHandleSearchVal}
                                                        handleContactClick={this.handleContactClick}
                                                    />
                                                }
                                            </div>
                                            : "Please enter 3 or more characters to search contacts"





                                        }
                                    </div>
                                </SimpleBar>

                                
                            </div>
                        </Form>
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="link" onClick={this.toggle}>{t('Close')}</Button>
                        <Button type="button" color="primary" onClick={this.createGroup}>{this.state.modalBtnLabel}</Button>
                    </ModalFooter>
                </Modal>
                {/* End add group Modal */}
                
            </React.Fragment>
        );
    }
}

const mapStateToProps = (state) => {
    const { groups, active_user, my_user, my_presence, my_name } = state.secureText.Chat;
    return { groups, active_user, my_user, my_presence, my_name };
};

export default (connect(mapStateToProps, { createGroup, createTempGroup, setActiveTab, activeUser, activeGroup, createTempSingleChat })(withTranslation()(ContactsDialog)));