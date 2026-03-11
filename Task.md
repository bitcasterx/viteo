# Implementation of a Video Conversion Backend

Imagine you are building a backend service for a video editing application like ours. Your task is to implement a system that accepts user video uploads, converts them into a lightweight browser-playable video format, allows users to query the status of conversion jobs and to download the converted video once ready. The backend should expose a GraphQL API for communication. The architecture, API design, and DevOps setup are up to the implementer.

Lines that are marked with (*) are optional. You may implement them if you have time or interest. If you feel that completing the Test Task requires more time than you can reasonably allocate, you may implement a bare minimum required for the API to function. However, please take into account the "Things that will be assessed" section.

When the code is ready, please deploy it to GitHub as a private repository and give us access (romikzo, icune, imposibrus, chiefgreg).

---

## Functional Requirements

- The API should support uploading a video file for conversion.
- The API should support querying the status of a conversion job, including:
  - Current job state (queued, processing, completed, failed)
  - Output video download URL once the conversion is ready
  - (*) Conversion progress (0–100%)
- Conversion should be performed asynchronously via Jobs, so multiple conversions should be able to run concurrently.
- Input videos can be of any type; output videos should be browser-playable (MP4, up to 720p; other optimizations of your choice).
- (*) Conversions history (job details, metadata) should be stored in a relational database.

## Technical Requirements

- Laravel should be used as the main framework.
- Lighthouse should be used for GraphQL.
- FFmpeg should be used for video conversion.
- Docker should be used to run the project locally.
- (*) MySQL/MariaDB should be used as a relational database.
- (*) A CI/CD pipeline should be configured with GitHub Actions to build and publish a Docker image and perform other tasks of your choice.
- (*) Minimal automated tests (unit or feature) are desirable.
- (*) Minimal static analysis is desirable.

Use of code-writing LLMs isn't encouraged; however, you may consult LLMs for specific parts. The goal of this task is to test your personal thinking and coding skills.

---

## Things that will be assessed

- Code readability and clarity of intentions (naming, abstractions, structure)
- Consistency across the application's code
- Architectural decomposition – think as if the system may later need to handle a high load, scaling, and new features
- Correct use of queues, jobs, and persistence
- Decisions affecting reliability and maintainability – retries, error handling, API schema choices, etc.
- DevOps awareness – Docker setup, reproducible environments, working CI/CD pipeline
