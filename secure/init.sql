
BEGIN TRANSACTION;

CREATE TABLE images (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    name TEXT NOT NULL UNIQUE,
	file_ext TEXT NOT NULL,
	uploader TEXT NOT NULL,
	description TEXT NOT NULL,
	citation TEXT
);

CREATE TABLE tags (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	name TEXT NOT NULL UNIQUE
);

CREATE TABLE image_tags (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	image_id INTEGER NOT NULL,
	tag_id INTEGER NOT NULL
);


INSERT INTO images (id, name, file_ext, uploader, description, citation) VALUES (1, 'LVEI Group 1', '.JPG','Stephanie Chow','Group Pictures from showcase', '(original work) Stephanie Chow');
INSERT INTO images (id, name, file_ext, uploader, description, citation) VALUES (2, 'LVEI Group Pics 2', '.JPG','Stephanie Chow','Group Pictures from showcase', '(original work) Stephanie Chow');
INSERT INTO images (id, name, file_ext, uploader, description, citation) VALUES (3, 'Mid semester Dinner 1', '.png','Person A','Group Pictures from Fall 2019 dinner', '(original work) Stephanie Chow');
INSERT INTO images (id, name, file_ext, uploader, description, citation) VALUES (4, 'Mid semester Dinner 2', '.png','Person A','Group Pictures from Fall 2019 dinner', '(original work) Stephanie Chow');
INSERT INTO images (id, name, file_ext, uploader, description, citation) VALUES (5, 'CSA Group Pic','.JPG','Person B','Group Pictures from CSA event', '(original work) Stephanie Chow');
INSERT INTO images (id, name, file_ext, uploader, description,citation) VALUES (6, 'CTAS Group Pic', '.JPG','Person B','Group Pictures from CTAS event', '(original work) Stephanie Chow');
INSERT INTO images (id, name, file_ext, uploader, description, citation) VALUES (7, 'Miyake Group Pic', '.JPG','Person C','Group Pictures from Miyake Dinner', '(original work) Stephanie Chow');
INSERT INTO images (id, name, file_ext, uploader, description, citation) VALUES (8, 'Miyake Selfie Group Pic', '.JPG','Person C','Group Pictures from Miyake Dinner', '(original work) Stephanie Chow');
INSERT INTO images (id, name, file_ext, uploader, description, citation) VALUES (9, 'Ice Skating Social', '.jpg','Person D','Group Pictures from Ice Skating Social', '(original work) Stephanie Chow');
INSERT INTO images (id, name, file_ext, uploader, description, citation) VALUES (10, 'Oh My Group Pic', '.jpg','Person B','Oh My Group Photo after Filming', '(original work) Stephanie Chow');

INSERT INTO tags(id,name) VALUES (1,'2019');
INSERT INTO tags(id,name) VALUES (2,'2020');
INSERT INTO tags(id,name) VALUES (3,'Showcase');
INSERT INTO tags(id,name) VALUES (4,'Socials');
INSERT INTO tags(id,name) VALUES (5,'Performances');
INSERT INTO tags(id,name) VALUES (6, 'Fall');
INSERT INTO tags(id,name) VALUES (7, 'Spring');

INSERT INTO image_tags(id, image_id, tag_id) VALUES (1,1,1);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (2,1,3);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (3,2,1);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (4,2,3);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (5,3,1);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (6,3,4);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (7,4,1);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (8,4,4);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (9,5,1);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (10,5,5);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (11,6,1);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (12,6,5);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (13,7,1);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (14,7,4);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (15,8,1);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (16,8,4);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (17,9,1);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (18,9,4);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (19,10,2);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (20,10,5);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (21,10,7);
INSERT INTO image_tags(id, image_id, tag_id) VALUES (22,9,6);

COMMIT;
