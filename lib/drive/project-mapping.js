import { isProjectId } from "../project-id";

export const DRIVE_FOLDERS = Object.freeze({
  root: process.env.HCDECOR_DRIVE_ROOT_FOLDER_ID || "",
  projects: process.env.HCDECOR_DRIVE_PROJECTS_FOLDER_ID || "",
  media: process.env.HCDECOR_DRIVE_MEDIA_FOLDER_ID || "",
});

export function projectFolderName(project) {
  if (!project || !isProjectId(project.projectId)) throw new Error("Canonical Project ID required");
  const title = String(project.title || "Untitled").trim().replace(/[\\/:*?"<>|]+/g, "-");
  return `${project.projectId} - ${title}`;
}

export function projectManifest(project, drive = {}) {
  if (!project || !isProjectId(project.projectId)) throw new Error("Canonical Project ID required");
  return {
    schema: "hcdecor.project.v2",
    projectId: project.projectId,
    title: project.title || "",
    status: project.status || "draft",
    drive: {
      folderId: drive.folderId || null,
      folderUrl: drive.folderUrl || null,
      mediaFolderId: drive.mediaFolderId || null,
    },
    cms: {
      wordpressId: project.wordpressId || null,
      wordpressUrl: project.wordpressUrl || null,
    },
    updatedAt: new Date().toISOString(),
  };
}
