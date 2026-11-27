import HomeController from './HomeController'
import Bible from './Bible'
import PostController from './PostController'
import ProfileController from './ProfileController'
import FollowController from './FollowController'
import ReportController from './ReportController'
import PostInteractionController from './PostInteractionController'
import AdminController from './AdminController'
import Settings from './Settings'
const Controllers = {
    HomeController: Object.assign(HomeController, HomeController),
Bible: Object.assign(Bible, Bible),
PostController: Object.assign(PostController, PostController),
ProfileController: Object.assign(ProfileController, ProfileController),
FollowController: Object.assign(FollowController, FollowController),
ReportController: Object.assign(ReportController, ReportController),
PostInteractionController: Object.assign(PostInteractionController, PostInteractionController),
AdminController: Object.assign(AdminController, AdminController),
Settings: Object.assign(Settings, Settings),
}

export default Controllers