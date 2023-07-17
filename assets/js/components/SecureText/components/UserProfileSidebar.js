import React, { useState } from 'react';
import { connect } from "react-redux";
import {Button, Card, Badge, Modal, ModalBody} from "reactstrap";

//Simple bar
import SimpleBar from "simplebar-react";

//components
import CustomCollapse from "./CustomCollapse";

//actions
import { closeUserSidebar, removeMemberFromGroup } from "../../../../js/redux/SecureText/actions";

//i18n
import { useTranslation } from 'react-i18next';

//image
import grp_img from "../../../../images/group_img.png";
import ContactPresence from "./ContactPresence";

function UserProfileSidebar(props) {

    const [isOpen1, setIsOpen1] = useState(true);
    const [isOpen2, setIsOpen2] = useState(true);
    const [isOpen3, setIsOpen3] = useState(true);
    const [files] = useState([
        { name: "Admin-A.zip", size: "12.5 MB", thumbnail: "ri-file-text-fill" },
        { name: "Image-1.jpg", size: "4.2 MB", thumbnail: "ri-image-fill" },
        { name: "Image-2.jpg", size: "3.1 MB", thumbnail: "ri-image-fill" },
        { name: "Landing-A.zip", size: "6.7 MB", thumbnail: "ri-file-text-fill" },
    ]);

    /* intilize t variable for multi language implementation */
    const { t } = useTranslation();

    const toggleCollapse1 = () => {
        setIsOpen1(!isOpen1);
        setIsOpen2(false);
        setIsOpen3(false);
    };

    const toggleCollapse2 = () => {
        setIsOpen2(!isOpen2);
        setIsOpen1(false);
        setIsOpen3(false);
    };

    const toggleCollapse3 = () => {
        setIsOpen3(!isOpen3);
        setIsOpen1(false);
        setIsOpen2(false);
    };

    // closes sidebar
    const closeuserSidebar = () => {
        props.closeUserSidebar();
    }

    // remove group member
    const [RemoveFromGroupmodal, setRemoveFromGroupModal] = useState(false);
    const toggleRemoveFromGroupModal = () => setRemoveFromGroupModal(!RemoveFromGroupmodal);
    const removeUserFromGroup = (memId) => {
        $("#target_rm_id").val(memId);
        toggleRemoveFromGroupModal();

    }
    function removeFromGroup(e){
        e.preventDefault();
        let id = $("#current_group_sel").val();
        let phyId = $("#target_rm_id").val();
        var r = "/ajax_removefrm_group/"+id+"?phyid="+phyId;
        if(id < 0){ //for temp groups which are yet not saved.
            props.removeMemberFromGroup(id, phyId);
            toggleRemoveFromGroupModal();
        }else {
            $.getJSON(r, function (data) {
                if (data.status == "1") {
                    props.removeMemberFromGroup(id, phyId);
                    toggleRemoveFromGroupModal();
                } else {
                    alert('You cannot remove this user from the group.');
                }
            });
        }
    }

    return (
        <React.Fragment>
            <div style={{ display: (props.userSidebar === true) ? "block" : "none" }} className="modal-user-class user-profile-sidebar">
                <div className="px-3 px-lg-4 pt-3 pt-lg-4">
                    <div className="user-chat-nav  text-end">
                        <Button color="none" type="button" onClick={closeuserSidebar} className="nav-btn" id="user-profile-hide">
                            <i className="ri-close-line"></i>
                        </Button>
                    </div>
                </div>

                <div className="text-center p-4 border-bottom">

                    <div className="mb-4 d-flex justify-content-center">
                        {
                            props.activeUser.profilePicture === "Null" ?
                                <div className="avatar-lg">
                                    <span className="avatar-title rounded-circle bg-soft-primary text-primary font-size-24">
                                        {props.activeUser.name.charAt(0)}
                                    </span>
                                </div>
                                :
                                <div className="me-3 ms-0 side-profile-image">
                                    <img src={(props.activeUser.isGroup === true) ? grp_img : "physicians/getPhysicianImg/"+props.activeUser.id} className="rounded-circle avatar-lg img-thumbnail" alt="" />
                                    {
                                        props.activeUser.isGroup === true? "" :
                                            <ContactPresence args={[ {presence: props.activeUser.presence, id: props.activeUser.id, isCovered: props.activeUser.isCovered || props.activeUser.covId ? true : false} ]} />
                                    }
                                </div>
                        }

                    </div>

                    <h5 className="font-size-16 mb-1 text-truncate">{props.activeUser.name}</h5>
                </div>
                {/* End profile user */}

                {/* Start user-profile-desc */}
                <SimpleBar style={{ maxHeight: "100%" }} className="p-4 user-profile-desc">
                    {/*
                    <div className="text-muted">
                        <p className="mb-4">"{t('If several languages coalesce, the grammar of the resulting language is more simple and regular than that of the individual.')}"</p>
                    </div>
*/}

                    <div id="profile-user-accordion" className="custom-accordion">
                        {
                            props.activeUser.isGroup !== true &&
                            <Card className="shadow-none border mb-2">
                                {/* import collaps */}
                                <CustomCollapse
                                    title="About"
                                    iconClass="ri-user-2-line"
                                    isOpen={isOpen1}
                                    toggleCollapse={toggleCollapse1}
                                >

                                    <div>
                                        <p className="text-muted mb-1">{t('Name')}</p>
                                        <h5 className="font-size-14">{props.activeUser.name}</h5>
                                    </div>
                                </CustomCollapse>
                            </Card>
                        }

                        {
                            (props.activeUser.isGroup === true && props.activeUser.members && props.activeUser.priv) &&
                            <Card className="mb-1 shadow-none border">
                                {/* import collaps */}
                                <CustomCollapse
                                    title="Members"
                                    iconClass="ri-group-line"
                                    isOpen={isOpen3}
                                    toggleCollapse={toggleCollapse3}
                                >
                                    {
                                        props.activeUser.members.map((member, key) =>
                                            member ?
                                                <Card className="p-2 mb-2" key={key}>
                                                    <div className="row align-items-center">
                                                        <div className="col-2 chat-avatar">
                                                            <img src={(member.presence == "group") ? grp_img : "physicians/getPhysicianImg/" + member.id } className="rounded-circle chat-user-img avatar-xs me-3" alt="" />
                                                            <ContactPresence args={[ {presence: member.presence, id: member.id, isCovered: member.isCovered || member.covId ? true : false} ]} />
                                                        </div>
                                                        <div className="col-6">
                                                            <div className="text-left">
                                                                <h5 className="font-size-14 mb-1">{member.name}</h5>
                                                            </div>
                                                        </div>
                                                        <div className="col-4" style={{ textAlign: "right" }}>
                                                            <ul className="list-inline mb-0 font-size-18">
                                                                {
                                                                    (props.activeUser.priv && props.activeUser.priv.remove && member.id != props.my_user) &&
                                                                        <li className="list-inline-item d-lg-inline-block">
                                                                            <Button type="button" title="Remove from Group"
                                                                                    color="none"
                                                                                    onClick={() => { removeUserFromGroup(member.id); }}
                                                                                    className="nav-btn remove-from-group text-muted px-1">
                                                                                <i className="ri-logout-box-r-line"></i>
                                                                            </Button>
                                                                        </li>
                                                                }
                                                                {
                                                                    (member.id == props.activeUser.owner) &&
                                                                    <h4>(Owner)</h4>
                                                                }
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </Card>
                                                : ""
                                        )
                                    }
                                </CustomCollapse>
                            </Card>
                        }
                    </div>
                </SimpleBar>
                {/* end user-profile-desc */}
            </div>
            <input type="hidden" id="target_rm_id" />

            {/* Leave group Modal */}
            {
                (props.activeUser.priv && props.activeUser.priv.remove) &&
                    <Modal tabIndex="-1" isOpen={RemoveFromGroupmodal} toggle={toggleRemoveFromGroupModal} centered>
                        <ModalBody>
                            <div className="text-left p-4">
                                <h4 className="text-truncate">Are you sure you want to remove a member from this group?</h4>
                                <div className="mt-5">
                                    <ul className="list-inline mb-1">
                                        <li className="list-inline-item px-2 me-2 ms-0">
                                            <button type="button" className="btn btn-danger avatar-sm rounded-circle"
                                                    onClick={toggleRemoveFromGroupModal}>
                                            <span className="avatar-title bg-transparent font-size-20">
                                                <i className="ri-close-fill"></i>
                                            </span>
                                            </button>
                                        </li>
                                        <li className="list-inline-item px-2">
                                            <button type="button" onClick={(e) => removeFromGroup(e)}
                                                    className="btn btn-success avatar-sm rounded-circle">
                                            <span className="avatar-title bg-transparent font-size-20">
                                                <i className="ri-check-line"></i>
                                            </span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </ModalBody>
                    </Modal>
            }
        </React.Fragment>
    );
}

const mapStateToProps = (state) => {
    const { users, active_user, my_user } = state.secureText.Chat;
    const { userSidebar } = state.secureText.Layout;
    return { users, active_user, userSidebar, my_user };
};

export default connect(mapStateToProps, { closeUserSidebar, removeMemberFromGroup })(UserProfileSidebar);