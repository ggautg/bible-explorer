import HomeController from './HomeController'
import Bible from './Bible'
import PostController from './PostController'
import ProfileController from './ProfileController'
import FollowController from './FollowController'
import PostInteractionController from './PostInteractionController'
import Settings from './Settings'
const Controllers = {
    HomeController: Object.assign(HomeController, HomeController),
Bible: Object.assign(Bible, Bible),
PostController: Object.assign(PostController, PostController),
ProfileController: Object.assign(ProfileController, ProfileController),
FollowController: Object.assign(FollowController, FollowController),
PostInteractionController: Object.assign(PostInteractionController, PostInteractionController),
Settings: Object.assign(Settings, Settings),
}

export default Controllers