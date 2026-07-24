import HomeController from './HomeController'
import Bible from './Bible'
import Settings from './Settings'
const Controllers = {
    HomeController: Object.assign(HomeController, HomeController),
Bible: Object.assign(Bible, Bible),
Settings: Object.assign(Settings, Settings),
}

export default Controllers