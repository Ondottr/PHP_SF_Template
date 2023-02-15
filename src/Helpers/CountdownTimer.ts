// noinspection MagicNumberJS
/*
 * Copyright © 2018-2023, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby
 * granted, provided that the above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED \"AS IS\" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE
 * INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER
 * RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

export default (countDownDate, element) => {
    // Update the countdown every 1 second
    const x = setInterval(() => {
        // Get today's date and time
        const now = new Date().getTime();

        // Find the distance between now and the countdown date
        const distance = countDownDate - now;

        // If the countdown is finished, write some text
        if (distance < 0) {
            clearInterval(x);
            return;
        }
        let timeStr = '';

        // Time calculations for days, hours, minutes and seconds
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));

        if (days > 0) timeStr += `${days}d `;

        const hours = Math.floor(
            (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60),
        );

        if (hours > 0) timeStr += `${hours}h `;

        if (days === 0) {
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

            if (minutes > 0) timeStr += `${minutes}m `;
        }

        if (hours === 0 && days === 0) {
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            if (seconds >= 0) timeStr += `${seconds}s `;
        }

        // Display the result in the element with id="demo"
        element.html(timeStr);
    }, 1000);
};

