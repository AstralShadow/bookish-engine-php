<?php
namespace Extend;

class Permissions
{

    const CanCreateRoles = 1; // not implemented
    const CanGiveRoles = 2; // not implemented

    const CanBlockUsers = 4; // not implemented
    const CanResolveReports = 8; // not implemented

    const CanApproveResources = 16; // includes full free resource access
    const CanCreateTags = 32; // global access
    const CanApproveTags = 64; // not given to anyone
    const CanProposeTags = 128; // global access

    // global access ones aren't yet checked.

}
