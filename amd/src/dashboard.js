// This file is part of Moodle - http://moodle.org/

import Ajax from 'core/ajax';
import Notification from 'core/notification';

/**
 * Loads the available-courses carousel on the dashboard.
 *
 * @module local_coursecarousel/dashboard
 */
export const init = () => {
    const regionMain = document.querySelector('#region-main');
    if (!regionMain) {
        return;
    }

    Ajax.call([{
        methodname: 'local_coursecarousel_get_available_courses',
        args: {},
    }])[0]
        .then(({html}) => regionMain.insertAdjacentHTML('afterbegin', html))
        .catch(Notification.exception);
};
