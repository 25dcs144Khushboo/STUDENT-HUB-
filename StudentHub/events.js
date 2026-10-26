let events = [];

let currentPage = 1;

const eventsPerPage = 4;

const studentId = 1;

const eventContainer =
    document.getElementById("eventContainer");

const searchInput =
    document.getElementById("searchInput");

const categoryFilter =
    document.getElementById("categoryFilter");

const sortSelect =
    document.getElementById("sortSelect");

const prevBtn =
    document.getElementById("prevBtn");

const nextBtn =
    document.getElementById("nextBtn");

const pageNumber =
    document.getElementById("pageNumber");


fetch("events.php")

    .then(response => {

        if (!response.ok) {
            throw new Error("Failed to load events");
        }

        return response.json();

    })

    .then(data => {

        events = data;

        displayEvents();

    })

    .catch(error => {

        eventContainer.innerHTML =
            `<p class="no-results">
                Error loading events.
            </p>`;

        console.log(error);

    });


function displayEvents() {

    let filteredEvents = [...events];

    let searchText =
        searchInput.value.toLowerCase();

    if (searchText !== "") {

        filteredEvents =
            filteredEvents.filter(event =>

                event.title
                    .toLowerCase()
                    .includes(searchText)

                ||

                event.description
                    .toLowerCase()
                    .includes(searchText)

            );

    }


    let category =
        categoryFilter.value;

    if (category !== "all") {

        filteredEvents =
            filteredEvents.filter(event =>
                event.category === category
            );

    }


    let sortValue =
        sortSelect.value;


    if (sortValue === "dateAsc") {

        filteredEvents.sort((a, b) =>
            new Date(a.date) -
            new Date(b.date)
        );

    }

    else if (sortValue === "dateDesc") {

        filteredEvents.sort((a, b) =>
            new Date(b.date) -
            new Date(a.date)
        );

    }

    else if (sortValue === "nameAsc") {

        filteredEvents.sort((a, b) =>
            a.title.localeCompare(b.title)
        );

    }

    else if (sortValue === "nameDesc") {

        filteredEvents.sort((a, b) =>
            b.title.localeCompare(a.title)
        );

    }


    let totalPages =
        Math.ceil(
            filteredEvents.length /
            eventsPerPage
        );


    if (
        currentPage > totalPages &&
        totalPages > 0
    ) {

        currentPage = totalPages;

    }


    let startIndex =
        (currentPage - 1) *
        eventsPerPage;

    let endIndex =
        startIndex +
        eventsPerPage;


    let currentEvents =
        filteredEvents.slice(
            startIndex,
            endIndex
        );


    eventContainer.innerHTML = "";


    if (currentEvents.length === 0) {

        eventContainer.innerHTML =
            `<p class="no-results">
                No events found.
            </p>`;

    }


    currentEvents.forEach(event => {

        let card =
            document.createElement("article");

        card.className =
            "event-card";


        card.innerHTML = `

            <div>

                <span class="category">
                    ${event.category}
                </span>

                <h3>
                    ${event.title}
                </h3>

                <p>
                    <strong>Date:</strong>
                    ${formatDate(event.date)}
                </p>

                <p>
                    <strong>Time:</strong>
                    ${event.time}
                </p>

                <p>
                    <strong>Venue:</strong>
                    ${event.venue}
                </p>

                <p>
                    ${event.description}
                </p>

            </div>

            <button
                onclick="registerEvent(${event.id})">
                Register
            </button>

        `;


        eventContainer.appendChild(card);

    });


    if (totalPages === 0) {

        pageNumber.textContent =
            "Page 0 of 0";

    }

    else {

        pageNumber.textContent =
            `Page ${currentPage} of ${totalPages}`;

    }


    prevBtn.disabled =
        currentPage === 1;


    nextBtn.disabled =
        currentPage === totalPages ||
        totalPages === 0;

}


function formatDate(date) {

    return new Date(date)
        .toLocaleDateString("en-GB", {

            day: "numeric",

            month: "long",

            year: "numeric"

        });

}


function registerEvent(eventId) {

    fetch("register.php", {

        method: "POST",

        headers: {
            "Content-Type": "application/json"
        },

        body: JSON.stringify({

            student_id: studentId,

            event_id: eventId

        })

    })

    .then(response =>
        response.json()
    )

    .then(data => {

        alert(data.message);

    })

    .catch(error => {

        alert("Registration failed.");

        console.log(error);

    });

}


searchInput.addEventListener(
    "input",
    () => {

        currentPage = 1;

        displayEvents();

    }
);


categoryFilter.addEventListener(
    "change",
    () => {

        currentPage = 1;

        displayEvents();

    }
);


sortSelect.addEventListener(
    "change",
    () => {

        currentPage = 1;

        displayEvents();

    }
);


prevBtn.addEventListener(
    "click",
    () => {

        if (currentPage > 1) {

            currentPage--;

            displayEvents();

        }

    }
);


nextBtn.addEventListener(
    "click",
    () => {

        currentPage++;

        displayEvents();

    }
);